<?php

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;

// The route under test: POST /api/profile/suggest-update
// Authorization check: $user->studentProfile !== null (must have completed onboarding)

// ── (1) Unauthenticated ───────────────────────────────────────────────────────

test('unauthenticated json request returns 401', function () {
    $response = $this->postJson(route('profile.suggest-update'), [
        'course_code' => 'ACCT 205',
        'status' => 'completed',
    ]);

    $response->assertStatus(401);
});

// ── (2) Authenticated but not authorized ─────────────────────────────────────

test('user without a student profile gets 403 on suggest-update', function () {
    $user = User::factory()->create(); // no StudentProfile — skipped onboarding

    $response = $this->actingAs($user)->postJson(route('profile.suggest-update'), [
        'course_code' => 'ACCT 205',
        'status' => 'completed',
    ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'This action is unauthorized.']);
});

test('unauthorized attempt is logged with user id and path', function () {
    Event::fake([MessageLogged::class]);

    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('profile.suggest-update'), [
        'course_code' => 'ACCT 205',
        'status' => 'completed',
    ]);

    Event::assertDispatched(MessageLogged::class, function (MessageLogged $e) use ($user) {
        return $e->level === 'warning'
            && str_contains($e->message, 'Unauthorized access attempt')
            && ($e->context['user_id'] ?? null) === $user->id
            && isset($e->context['path'], $e->context['method']);
    });
});

// ── (3) Authenticated and authorized ─────────────────────────────────────────

test('user with a student profile can call suggest-update', function () {
    $user = User::factory()->create();

    StudentProfile::create([
        'user_id' => $user->id,
        'full_name' => 'Test Student',
        'degree' => 'bsba',
        'catalog_year' => 'post_2024',
        'admit_term' => 'Fall 2024',
        'expected_graduation' => 'Spring 2028',
    ]);

    $response = $this->actingAs($user)->postJson(route('profile.suggest-update'), [
        'course_code' => 'ACCT 205',
        'status' => 'completed',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true, 'course_code' => 'ACCT 205']);
});

// ── (4) dismiss-prompt follows the same gate ─────────────────────────────────

test('user without a profile gets 403 on dismiss-prompt', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('profile.dismiss-prompt'))
        ->assertStatus(403);
});

test('user with a profile can dismiss the semester prompt', function () {
    $user = User::factory()->create();

    StudentProfile::create([
        'user_id' => $user->id,
        'full_name' => 'Test Student',
        'degree' => 'bsba',
        'catalog_year' => 'post_2024',
        'admit_term' => 'Fall 2024',
        'expected_graduation' => 'Spring 2028',
    ]);

    $this->actingAs($user)
        ->postJson(route('profile.dismiss-prompt'))
        ->assertOk()
        ->assertJson(['success' => true]);
});
