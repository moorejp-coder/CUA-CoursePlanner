<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            $redirect = $request->user()->studentProfile
                ? route('dashboard', absolute: false).'?verified=1'
                : route('onboarding');

            return redirect()->intended($redirect);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        $redirect = $request->user()->studentProfile
            ? route('dashboard', absolute: false).'?verified=1'
            : route('onboarding');

        return redirect()->intended($redirect);
    }
}
