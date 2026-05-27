<?php

use App\Http\Middleware\Honeypot;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('signup_ip|127.0.0.1');
    Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);
});

// ── IP rate limit: 3 accounts per hour ───────────────────────────────────────

test('fourth registration from same ip is blocked', function () {
    for ($i = 0; $i < 3; $i++) {
        $this->post('/register', [
            'name' => 'Student '.$i,
            'email' => "student{$i}@cua.edu",
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ]);
    }

    $response = $this->post('/register', [
        'name' => 'Blocked',
        'email' => 'blocked@cua.edu',
        'password' => 'correct-horse-battery-staple',
        'password_confirmation' => 'correct-horse-battery-staple',
    ]);

    $response->assertStatus(429);
    expect(User::whereEmail('blocked@cua.edu')->exists())->toBeFalse();
});

test('rate limit is per ip so counters are independent across ips', function () {
    for ($i = 0; $i < 3; $i++) {
        RateLimiter::hit('signup_ip|127.0.0.1', 3600);
    }

    expect(RateLimiter::tooManyAttempts('signup_ip|127.0.0.1', 3))->toBeTrue();
    expect(RateLimiter::tooManyAttempts('signup_ip|10.0.0.1', 3))->toBeFalse();
});

// ── Email verification required ───────────────────────────────────────────────

test('unverified user is redirected to verify email page', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get('/chat')
        ->assertRedirect(route('verification.notice'));
});

test('verified user can access protected routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/chat')->assertOk();
});

// ── Honeypot catches bots ─────────────────────────────────────────────────────

test('registration with honeypot field filled is rejected with 422', function () {
    $response = $this->post('/register', [
        'name' => 'Bot',
        'email' => 'bot@cua.edu',
        'password' => 'anything',
        'password_confirmation' => 'anything',
        Honeypot::FIELD => 'i-am-a-bot',
    ]);

    $response->assertStatus(422);
    expect(User::whereEmail('bot@cua.edu')->exists())->toBeFalse();
});

test('registration with empty honeypot field proceeds normally', function () {
    $response = $this->post('/register', [
        'name' => 'Student',
        'email' => 'student@cua.edu',
        'password' => 'correct-horse-battery-staple',
        'password_confirmation' => 'correct-horse-battery-staple',
        Honeypot::FIELD => '',
    ]);

    $response->assertRedirect(route('onboarding'));
    expect(User::whereEmail('student@cua.edu')->exists())->toBeTrue();
});
