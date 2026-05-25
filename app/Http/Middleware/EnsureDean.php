<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDean
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::user()?->isDean()) {
            abort(403, 'Access restricted to Busch School administrators.');
        }

        return $next($request);
    }
}
