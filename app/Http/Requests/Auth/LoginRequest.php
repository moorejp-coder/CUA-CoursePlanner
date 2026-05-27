<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Notifications\AccountUnlockNotification;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $domain = '@'.config('app.allowed_email_domain');
                    if (! str_ends_with(strtolower((string) $value), $domain)) {
                        $fail('This application is only available to Catholic University of America students and staff.');
                    }
                },
            ],
            'password' => ['required', 'string', 'max:72'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIpNotRateLimited();
        $this->ensureAccountNotLocked();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->recordFailedAttempt();

            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        RateLimiter::clear($this->ipKey());
        RateLimiter::clear($this->accountKey());
    }

    /**
     * @throws ValidationException
     */
    private function ensureIpNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->ipKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->ipKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function ensureAccountNotLocked(): void
    {
        $user = User::whereEmail($this->string('email'))->first();

        if (! ($user && $user->login_locked_at !== null)) {
            return;
        }

        if ($user->login_locked_at->addMinutes(30)->isPast()) {
            $user->update(['login_locked_at' => null]);
            RateLimiter::clear($this->accountKey());

            return;
        }

        $minutesLeft = (int) max(1, now()->diffInMinutes($user->login_locked_at->addMinutes(30)));

        throw ValidationException::withMessages([
            'email' => "Your account is temporarily locked due to too many failed login attempts. It will unlock automatically in about {$minutesLeft} minute(s), or check your email for an unlock link.",
        ]);
    }

    private function recordFailedAttempt(): void
    {
        RateLimiter::hit($this->ipKey(), 900);

        $accountAttempts = RateLimiter::attempts($this->accountKey()) + 1;
        RateLimiter::hit($this->accountKey(), 86400);

        if ($accountAttempts >= 10) {
            $user = User::whereEmail($this->string('email'))->first();
            if ($user && $user->login_locked_at === null) {
                $user->update(['login_locked_at' => now()]);
                $lockedAt = now();
                $unlockUrl = URL::temporarySignedRoute(
                    'login.unlock',
                    $lockedAt->copy()->addHours(2),
                    ['user' => $user->id]
                );
                $user->notify(new AccountUnlockNotification($unlockUrl, $this->ip() ?? '0.0.0.0', $lockedAt));
            }
        }
    }

    private function ipKey(): string
    {
        return 'login_ip|'.$this->ip();
    }

    private function accountKey(): string
    {
        return 'login_acct|'.Str::lower($this->string('email'));
    }
}
