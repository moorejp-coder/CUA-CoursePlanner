<?php

use App\Models\User;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    Notification::fake();
    Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);
});

// ── Per-email rate limit on reset link requests ───────────────────────────────

test('fourth password reset request for same email is blocked', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 3; $i++) {
        $this->withoutMiddleware(ThrottleRequests::class)
            ->post('/forgot-password', ['email' => $user->email]);
    }

    $response = $this->withoutMiddleware(ThrottleRequests::class)
        ->post('/forgot-password', ['email' => $user->email]);

    $response->assertSessionHasErrors('email');
    expect($response->getSession()->get('errors')->first('email'))
        ->toContain('Too many password reset requests');
});

test('per-email limit does not block reset requests for different emails', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $emailKeyA = 'pwd_reset_email|'.strtolower($userA->email);

    for ($i = 0; $i < 3; $i++) {
        RateLimiter::hit($emailKeyA, 3600);
    }

    // userA is blocked
    expect(RateLimiter::tooManyAttempts($emailKeyA, 3))->toBeTrue();

    // userB is unaffected
    $response = $this->withoutMiddleware(ThrottleRequests::class)
        ->post('/forgot-password', ['email' => $userB->email]);

    $response->assertSessionMissing('errors');
    $response->assertSessionHas('status');
});

// ── Email existence not revealed ──────────────────────────────────────────────

test('forgot password returns same response for registered and non-registered email', function () {
    $user = User::factory()->create();

    $responseRegistered = $this->withoutMiddleware(ThrottleRequests::class)
        ->post('/forgot-password', ['email' => $user->email]);

    $responseUnknown = $this->withoutMiddleware(ThrottleRequests::class)
        ->post('/forgot-password', ['email' => 'nobody@cua.edu']);

    // Both get the same session status key — existence of the account is not revealed
    $responseRegistered->assertSessionHas('status');
    $responseUnknown->assertSessionHas('status');

    expect($responseRegistered->getSession()->get('status'))
        ->toBe($responseUnknown->getSession()->get('status'));
});

// ── Reset token submission rate limiting ──────────────────────────────────────

test('reset token endpoint is rate limited per ip', function () {
    // ThrottleRequests hashes named-limiter keys as md5($limiterName . $limit->key)
    $cacheKey = md5('password-reset-token'.'pwd_reset_token|127.0.0.1');
    for ($i = 0; $i < 10; $i++) {
        RateLimiter::hit($cacheKey, 3600);
    }

    $user = User::factory()->create();
    $token = app('auth.password.broker')->createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewR3set_XYZ!',
        'password_confirmation' => 'NewR3set_XYZ!',
    ]);

    $response->assertStatus(429);
});

test('valid reset token succeeds when not rate limited', function () {
    RateLimiter::clear(md5('password-reset-token'.'pwd_reset_token|127.0.0.1'));

    $user = User::factory()->create();
    $token = app('auth.password.broker')->createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewR3set_XYZ!',
        'password_confirmation' => 'NewR3set_XYZ!',
    ]);

    $response->assertRedirect(route('login'));
});
