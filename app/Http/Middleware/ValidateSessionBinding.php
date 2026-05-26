<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateSessionBinding
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $ip = $request->ip();
        $ua = $request->userAgent() ?? '';

        $storedIp = $request->session()->get('_bind_ip');
        $storedUa = $request->session()->get('_bind_ua');

        if ($storedIp === null) {
            // First authenticated request — stamp the binding onto the session.
            $request->session()->put('_bind_ip', $ip);
            $request->session()->put('_bind_ua', $ua);

            return $next($request);
        }

        // User-agent change is a reliable signal: browsers don't swap mid-session.
        // Treat it as session hijacking and force re-authentication.
        if ($storedUa !== $ua) {
            Log::warning('Session invalidated: user-agent mismatch', [
                'user_id' => $request->user()->id,
                'ip' => $ip,
            ]);

            return $this->forceReauth($request);
        }

        // IP changes legitimately happen on mobile/VPN transitions, so log
        // the anomaly but update the binding rather than killing the session.
        if ($storedIp !== $ip) {
            Log::warning('Session IP address changed', [
                'user_id' => $request->user()->id,
                'previous_ip' => $storedIp,
                'current_ip' => $ip,
            ]);

            $request->session()->put('_bind_ip', $ip);
        }

        return $next($request);
    }

    private function forceReauth(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => 'Your session was invalidated for security reasons. Please log in again.',
        ]);
    }
}
