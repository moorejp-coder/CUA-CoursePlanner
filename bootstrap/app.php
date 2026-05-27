<?php

use App\Http\Middleware\DetectAttackPatterns;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\Honeypot;
use App\Http\Middleware\SanitizeIntendedRedirect;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ValidateSessionBinding;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts();
        $middleware->trustProxies(at: '*');
        $middleware->append(ForceHttps::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->web(append: [
            ValidateSessionBinding::class,
            DetectAttackPatterns::class,
            Honeypot::class,
            SanitizeIntendedRedirect::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force JSON for all /api/* routes regardless of Accept header,
        // so fetch() calls without an explicit Accept header still get JSON.
        $exceptions->shouldRenderJsonWhen(function (Request $request): bool {
            return str_starts_with($request->path(), 'api/');
        });

        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if (! str_starts_with($request->path(), 'api/')) {
                return null; // let the default renderer produce the web error page
            }

            // ValidationException carries safe, user-facing field errors — let Laravel
            // format those itself (returns {"message":"...", "errors":{...}}, no traces).
            if ($e instanceof ValidationException) {
                return null;
            }

            if ($e instanceof AuthenticationException) {
                return null;
            }

            $status = $e instanceof HttpException ? $e->getStatusCode() : 500;

            // Log full diagnostics server-side for unexpected errors.
            // Nothing logged here is ever sent to the client.
            if ($status >= 500) {
                Log::error('Unhandled API exception', [
                    'class' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => $request->user()?->id,
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // Generic client message — no stack traces, file paths, or DB details.
            $message = match (true) {
                $status === 401 => 'Unauthenticated.',
                $status === 403 => 'This action is unauthorized.',
                $status === 404 => 'The requested resource was not found.',
                $status === 405 => 'Method not allowed.',
                $status === 429 => 'Too many requests. Please slow down.',
                $status >= 500 => 'An unexpected error occurred. Please try again.',
                default => 'An error occurred.',
            };

            return response()->json(['message' => $message], $status);
        });
    })->create();
