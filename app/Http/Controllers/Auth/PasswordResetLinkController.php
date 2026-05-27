<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        // Per-email rate limit: 3 requests per hour.
        // Always increment (regardless of whether the email is registered) so that
        // an attacker cannot determine account existence from the rate-limit response.
        $emailKey = 'pwd_reset_email|'.Str::lower($request->string('email'));

        if (RateLimiter::tooManyAttempts($emailKey, 3)) {
            throw ValidationException::withMessages([
                'email' => 'Too many password reset requests. Please wait before trying again.',
            ]);
        }

        RateLimiter::hit($emailKey, 3600);

        // Always return the same response — never reveal whether the email is registered.
        // The broker still sends the link only when a matching account is found.
        Password::sendResetLink($request->only('email'));

        return back()->with('status', __('passwords.sent'));
    }
}
