<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeIntendedRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        $intended = $request->session()->get('url.intended');

        if ($intended !== null) {
            $host = parse_url($intended, PHP_URL_HOST);
            if ($host !== null && $host !== $request->getHost()) {
                $request->session()->forget('url.intended');
            }
        }

        return $next($request);
    }
}
