<?php

use App\Models\User;
use App\Notifications\AccountUnlockNotification;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    RateLimiter::clear('login_ip|127.0.0.1');
    Notification::fake();
});

// ── Error message uniformity ──────────────────────────────────────────────────

test('wrong password returns unified error message', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email' => 'Invalid email or password.']);
});

test('non-existent email returns unified error message', function () {
    $response = $this->post('/login', [
        'email' => 'nobody@cua.edu',
        'password' => 'anything',
    ]);

    $response->assertSessionHasErrors(['email' => 'Invalid email or password.']);
});

// ── IP-based rate limiting ────────────────────────────────────────────────────

test('ip is blocked after 5 failed attempts', function () {
    // Pre-populate the IP limiter to 5 (simulates 5 prior failures from this IP)
    for ($i = 0; $i < 5; $i++) {
        RateLimiter::hit('login_ip|127.0.0.1', 900);
    }

    $user = User::factory()->create();
    $this->withoutMiddleware(ThrottleRequests::class)
        ->post('/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    expect(RateLimiter::tooManyAttempts('login_ip|127.0.0.1', 5))->toBeTrue();
});

test('ip block is independent of which account is targeted', function () {
    // Pre-fill the IP limiter — 5 failures from this IP
    for ($i = 0; $i < 5; $i++) {
        RateLimiter::hit('login_ip|127.0.0.1', 900);
    }

    // A completely different account is still blocked from the same IP
    $other = User::factory()->create();
    $this->withoutMiddleware(ThrottleRequests::class)
        ->post('/login', ['email' => $other->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    expect(RateLimiter::tooManyAttempts('login_ip|127.0.0.1', 5))->toBeTrue();
});

test('successful login clears the ip rate limiter', function () {
    $user = User::factory()->create(['password' => bcrypt('correct')]);

    // 4 failures
    for ($i = 0; $i < 4; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    // Success on 5th (IP limiter has 4 hits, not yet blocked)
    $this->withoutMiddleware(ThrottleRequests::class)
        ->post('/login', ['email' => $user->email, 'password' => 'correct'])
        ->assertRedirect();

    expect(RateLimiter::attempts('login_ip|127.0.0.1'))->toBe(0);
});

// ── Account-level lockout ─────────────────────────────────────────────────────

test('account is locked and unlock email sent after 10 failed attempts', function () {
    $user = User::factory()->create();

    // Bypass route throttle; clear IP limiter between attempts so only the
    // per-account counter drives the lockout logic.
    for ($i = 0; $i < 10; $i++) {
        RateLimiter::clear('login_ip|127.0.0.1');
        $this->withoutMiddleware(ThrottleRequests::class)
            ->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    $user->refresh();
    expect($user->login_locked_at)->not->toBeNull();
    Notification::assertSentTo($user, AccountUnlockNotification::class);
});

test('locked account cannot log in even with correct password', function () {
    $user = User::factory()->create([
        'login_locked_at' => now(),
        'password' => bcrypt('correct'),
    ]);

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'correct']);

    $response->assertSessionHasErrors('email');
    expect($response->getSession()->get('errors')->first('email'))
        ->toContain('locked');
});

test('account auto-unlocks after 30 minutes', function () {
    $user = User::factory()->create([
        'login_locked_at' => now()->subMinutes(31),
        'password' => bcrypt('correct'),
    ]);

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'correct']);

    $response->assertRedirect();
    $user->refresh();
    expect($user->login_locked_at)->toBeNull();
});

test('unlock notification is only sent once when account first locks', function () {
    $user = User::factory()->create(['login_locked_at' => now()]);

    // More attempts against already-locked account → no new notification
    $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);

    Notification::assertNothingSent();
});

// ── Unlock flow ───────────────────────────────────────────────────────────────

test('valid signed unlock link clears the account lock', function () {
    $user = User::factory()->create(['login_locked_at' => now()]);

    $url = URL::temporarySignedRoute('login.unlock', now()->addHours(2), ['user' => $user->id]);

    $response = $this->get($url);

    $response->assertRedirect(route('login'));
    $user->refresh();
    expect($user->login_locked_at)->toBeNull();
});

test('tampered unlock link is rejected', function () {
    $user = User::factory()->create(['login_locked_at' => now()]);

    $url = URL::temporarySignedRoute('login.unlock', now()->addHours(2), ['user' => $user->id]);

    $response = $this->get($url.'&tamper=1');

    $response->assertStatus(403);
    $user->refresh();
    expect($user->login_locked_at)->not->toBeNull();
});

test('expired unlock link is rejected', function () {
    $user = User::factory()->create(['login_locked_at' => now()]);

    $url = URL::temporarySignedRoute('login.unlock', now()->subHour(), ['user' => $user->id]);

    $response = $this->get($url);

    $response->assertStatus(403);
});

test('password reset clears account lock', function () {
    Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);

    $user = User::factory()->create(['login_locked_at' => now()]);
    $token = app('auth.password.broker')->createToken($user);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewR3set_XYZ!',
        'password_confirmation' => 'NewR3set_XYZ!',
    ]);

    $user->refresh();
    expect($user->login_locked_at)->toBeNull();
});
