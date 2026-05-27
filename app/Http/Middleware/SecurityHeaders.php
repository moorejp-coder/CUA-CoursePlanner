<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent the app from being embedded in any frame or iframe.
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent browsers from MIME-sniffing a response away from the declared Content-Type.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Only send the origin (no path/query) for cross-origin requests; full URL for same-origin.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable every browser feature this app does not use.
        $response->headers->set(
            'Permissions-Policy',
            'accelerometer=(), ambient-light-sensor=(), autoplay=(), battery=(), camera=(), '.
            'display-capture=(), document-domain=(), encrypted-media=(), fullscreen=(), '.
            'geolocation=(), gyroscope=(), magnetometer=(), microphone=(), midi=(), '.
            'payment=(), picture-in-picture=(), publickey-credentials-get=(), '.
            'screen-wake-lock=(), usb=(), web-share=(), xr-spatial-tracking=()'
        );

        // Restrict where scripts, styles, fonts, images, and connections can come from.
        // Fonts are self-hosted in /public/fonts — no external font CDN allowances needed.
        // unsafe-inline / unsafe-eval are required by Alpine.js and Vite's dev HMR overlay.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; ".
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; ".
            "style-src 'self' 'unsafe-inline'; ".
            "font-src 'self'; ".
            "img-src 'self' data:; ".
            "connect-src 'self'; ".
            "frame-ancestors 'none'; ".
            "base-uri 'self'; ".
            "form-action 'self'"
        );

        // HSTS: instruct browsers to always use HTTPS for the next year.
        // Omitted on local/testing so plain-HTTP dev sessions are not poisoned.
        if (! app()->environment('local', 'testing')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
