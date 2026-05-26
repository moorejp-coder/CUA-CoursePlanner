<?php

namespace App\Http\Concerns;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Adds a single authorizeAccess() method that controllers call at the top of any
 * action to enforce authentication (401) and resource-level authorization (403).
 *
 * Usage:
 *   $this->authorizeAccess($request, fn ($user) => $user->id === $resource->user_id);
 */
trait AuthorizesAccess
{
    /**
     * @param  Closure(mixed): bool  $check  Receives the authenticated User; return true to allow.
     */
    protected function authorizeAccess(Request $request, Closure $check): void
    {
        $user = $request->user();

        if (! $user) {
            Log::warning('Unauthenticated access attempt', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
            ]);

            abort(401, 'Unauthenticated.');
        }

        if (! $check($user)) {
            Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            abort(403, 'This action is unauthorized.');
        }
    }
}
