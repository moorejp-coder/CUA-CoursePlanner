<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Password::defaults(function () {
            return Password::min(8)
                ->uncompromised();
        });

        RateLimiter::for('form-submissions', function (Request $request) {
            return [
                Limit::perMinute(10)->by('user:'.($request->user()?->id ?: 'guest')),
                Limit::perMinute(10)->by('ip:'.$request->ip()),
            ];
        });
    }
}
