<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Honeypot
{
    public const FIELD = 'contact_phone';

    public function handle(Request $request, Closure $next): Response
    {
        // JSON API endpoints don't render HTML forms — no honeypot field to check.
        if ($request->is('api/*') || $request->expectsJson()) {
            return $next($request);
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $value = $request->input(self::FIELD);

            if ($value !== null && $value !== '') {
                Log::warning('Honeypot triggered', [
                    'ip' => $request->ip(),
                    'user_id' => $request->user()?->id,
                    'url' => $request->fullUrl(),
                ]);

                abort(422, 'Submission rejected.');
            }
        }

        return $next($request);
    }
}
