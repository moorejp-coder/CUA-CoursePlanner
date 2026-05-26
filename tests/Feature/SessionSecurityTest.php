<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

// ── (1) Session ID regeneration on login ─────────────────────────────────────

test('session id is regenerated on login', function () {
    $user = User::factory()->create();

    $before = session()->getId();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password1',
    ]);

    $this->assertNotEquals($before, session()->getId());
});

// ── (2) Session binding middleware ───────────────────────────────────────────

test('session binding is stamped on first authenticated request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])
        ->get('/profile');

    $this->assertEquals('192.168.1.1', session('_bind_ip'));
});

test('user agent change invalidates the session', function () {
    $user = User::factory()->create();

    // Establish binding with one user agent
    $this->actingAs($user)
        ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Legitimate Browser)'])
        ->get('/profile');

    // Same session, different user agent — should be kicked out
    $response = $this->actingAs($user)
        ->withHeaders(['User-Agent' => 'AttackerBot/1.0'])
        ->get('/profile');

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('ip change is logged but session remains valid', function () {
    $user = User::factory()->create();

    // Establish binding
    $this->actingAs($user)
        ->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
        ->get('/profile');

    // IP changes (e.g. mobile network handoff) — session should survive
    $response = $this->actingAs($user)
        ->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
        ->get('/profile');

    $response->assertOk();
    $this->assertEquals('10.0.0.2', session('_bind_ip'));
});

// ── (3) Log out everywhere ────────────────────────────────────────────────────

test('logout everywhere deletes all sessions for the user', function () {
    $user = User::factory()->create();

    // Seed two fake sessions for this user in the DB
    DB::table('sessions')->insert([
        ['id' => 'session-a', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time(), 'ip_address' => '1.2.3.4', 'user_agent' => 'A'],
        ['id' => 'session-b', 'user_id' => $user->id, 'payload' => 'x', 'last_activity' => time(), 'ip_address' => '1.2.3.5', 'user_agent' => 'B'],
    ]);

    $this->assertDatabaseCount('sessions', 2);

    $this->actingAs($user)
        ->post(route('logout.everywhere'))
        ->assertRedirect('/');

    $this->assertDatabaseCount('sessions', 0);
    $this->assertGuest();
});

test('logout everywhere redirects to home with status message', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout.everywhere'))
        ->assertRedirect('/')
        ->assertSessionHas('status', 'You have been signed out of all devices.');
});
