<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Reduce remember-me lifetime from Laravel's 400-day default to 30 days.
        // Students should re-authenticate at least monthly; 400 days outlasts a semester.
        Auth::guard('web')->setRememberDuration(43200);

        Password::defaults(function () {
            return Password::min(8)
                ->uncompromised();
        });

        RateLimiter::for('form-submissions', function (Request $request) {
            return [
                Limit::perMinute(30)->by('user:'.($request->user()?->id ?: 'guest')),
                Limit::perMinute(30)->by('ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('signup-ip', function (Request $request) {
            return Limit::perHour(10)->by('signup_ip|'.$request->ip());
        });

        // 10 token submissions per IP per hour — anti-brute-force on reset tokens
        RateLimiter::for('password-reset-token', function (Request $request) {
            return Limit::perHour(10)->by('pwd_reset_token|'.$request->ip());
        });
    }
}
