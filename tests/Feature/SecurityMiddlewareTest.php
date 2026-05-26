<?php

use App\Http\Middleware\Honeypot;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

// ── DetectAttackPatterns ──────────────────────────────────────────────────────

test('clean input passes attack pattern detection', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', ['name' => 'Jane Doe', 'email' => $user->email])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');
});

test('GET requests are not scanned for attack patterns', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk();
});

test('SQL injection in input is blocked', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => "' OR '1'='1",
            'email' => $user->email,
        ])
        ->assertStatus(422);
});

test('UNION SELECT injection is blocked', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'x UNION SELECT password FROM users',
            'email' => $user->email,
        ])
        ->assertStatus(422);
});

test('XSS script tag is blocked', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => '<script>alert(1)</script>',
            'email' => $user->email,
        ])
        ->assertStatus(422);
});

test('javascript: URI is blocked', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'javascript:alert(1)',
            'email' => $user->email,
        ])
        ->assertStatus(422);
});

test('oversized input is blocked', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => str_repeat('a', 10_001),
            'email' => $user->email,
        ])
        ->assertStatus(422);
});

test('null byte in input is blocked', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => "hello\x00world",
            'email' => $user->email,
        ])
        ->assertStatus(422);
});

// ── Honeypot ──────────────────────────────────────────────────────────────────

test('form without honeypot field passes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', ['name' => 'Jane Doe', 'email' => $user->email])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');
});

test('empty honeypot field passes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'Jane Doe',
            'email' => $user->email,
            Honeypot::FIELD => '',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');
});

test('filled honeypot field is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'Jane Doe',
            'email' => $user->email,
            Honeypot::FIELD => 'bot@example.com',
        ])
        ->assertStatus(422);
});

// ── form-submissions rate limiter ─────────────────────────────────────────────

test('ten form submissions per minute are allowed', function () {
    $user = User::factory()->create();
    RateLimiter::clear('user:'.$user->id);
    RateLimiter::clear('ip:127.0.0.1');

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Jane Doe', 'email' => $user->email])
            ->assertStatus(302);
    }
});

test('eleventh form submission within a minute is throttled', function () {
    $user = User::factory()->create();
    RateLimiter::clear('user:'.$user->id);
    RateLimiter::clear('ip:127.0.0.1');

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Jane Doe', 'email' => $user->email]);
    }

    $this->actingAs($user)
        ->patch('/profile', ['name' => 'Jane Doe', 'email' => $user->email])
        ->assertStatus(429);
});
