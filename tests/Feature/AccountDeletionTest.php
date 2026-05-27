<?php

use App\Models\StudentCourse;
use App\Models\StudentProfile;
use App\Models\User;
use App\Notifications\AccountDeletedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    Notification::fake();
    Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);
});

// ── Core deletion ──────────────────────────────────────────────────────────────

test('account deletion removes the user row', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/profile', ['password' => 'Password1'])
        ->assertRedirect();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('account deletion cascades to student_profile', function () {
    $user = User::factory()->create();

    StudentProfile::create([
        'user_id' => $user->id,
        'full_name' => 'Test Student',
        'degree' => 'bsba',
        'catalog_year' => 'post_2024',
        'admit_term' => 'Fall 2023',
        'expected_graduation' => 'Spring 2027',
        'credits_completed' => 30,
    ]);

    expect(StudentProfile::where('user_id', $user->id)->exists())->toBeTrue();

    $this->actingAs($user)->delete('/profile', ['password' => 'Password1']);

    expect(StudentProfile::where('user_id', $user->id)->exists())->toBeFalse();
});

test('account deletion cascades to all student_courses', function () {
    $user = User::factory()->create();

    foreach (['ACCT 205', 'ACCT 206', 'FIN 226'] as $code) {
        StudentCourse::create([
            'user_id' => $user->id,
            'course_code' => $code,
            'course_name' => $code,
            'requirement_category' => 'business_core',
            'status' => 'completed',
        ]);
    }

    expect(StudentCourse::where('user_id', $user->id)->count())->toBe(3);

    $this->actingAs($user)->delete('/profile', ['password' => 'Password1']);

    expect(StudentCourse::where('user_id', $user->id)->count())->toBe(0);
});

test('account deletion removes sessions from the sessions table', function () {
    $user = User::factory()->create();

    DB::table('sessions')->insert([
        'id' => 'test-session-abc',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
        'payload' => 'test',
        'last_activity' => time(),
    ]);

    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(1);

    $this->actingAs($user)->delete('/profile', ['password' => 'Password1']);

    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
});

test('account deletion removes password reset tokens for the user email', function () {
    $user = User::factory()->create();

    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => bcrypt('some-token'),
        'created_at' => now(),
    ]);

    expect(DB::table('password_reset_tokens')->where('email', $user->email)->count())->toBe(1);

    $this->actingAs($user)->delete('/profile', ['password' => 'Password1']);

    expect(DB::table('password_reset_tokens')->where('email', $user->email)->count())->toBe(0);
});

test('account deletion clears per-email rate limiter keys', function () {
    $user = User::factory()->create();
    $lower = strtolower($user->email);

    RateLimiter::hit('login_acct|'.$lower, 3600);
    RateLimiter::hit('pwd_reset_email|'.$lower, 3600);

    expect(RateLimiter::attempts('login_acct|'.$lower))->toBeGreaterThan(0);

    $this->actingAs($user)->delete('/profile', ['password' => 'Password1']);

    expect(RateLimiter::attempts('login_acct|'.$lower))->toBe(0);
    expect(RateLimiter::attempts('pwd_reset_email|'.$lower))->toBe(0);
});

// ── Confirmation email ─────────────────────────────────────────────────────────

test('account deletion sends a confirmation email', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/profile', ['password' => 'Password1']);

    Notification::assertSentTo($user, AccountDeletedNotification::class);
});

test('confirmation email contains the deletion manifest', function () {
    $user = User::factory()->create();

    StudentCourse::create([
        'user_id' => $user->id,
        'course_code' => 'ACCT 205',
        'course_name' => 'Financial Accounting',
        'requirement_category' => 'business_core',
        'status' => 'completed',
    ]);

    $this->actingAs($user)->delete('/profile', ['password' => 'Password1']);

    Notification::assertSentTo($user, AccountDeletedNotification::class, function ($notification) {
        $mail = $notification->toMail(new User);
        $rendered = implode("\n", array_column($mail->introLines, 'line') + $mail->introLines);

        return str_contains($rendered, 'permanently deleted')
            && $mail->subject === 'Your Busch Course Planner account has been permanently deleted';
    });
});

// ── Confirmation page ─────────────────────────────────────────────────────────

test('after deletion the user is redirected to the confirmation page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/profile', ['password' => 'Password1'])
        ->assertRedirect(route('account.deleted', ['courses' => 0, 'profile' => 0]));
});

test('confirmation page is publicly accessible without authentication', function () {
    $this->get(route('account.deleted', ['courses' => 3, 'profile' => 1]))
        ->assertOk()
        ->assertSee('Account Permanently Deleted')
        ->assertSee('3 deleted')
        ->assertSee('Deleted');
});

// ── Auth & validation guards ───────────────────────────────────────────────────

test('deletion requires correct password confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/profile', ['password' => 'wrong-password'])
        ->assertSessionHasErrorsIn('userDeletion', ['password']);

    expect(User::find($user->id))->not->toBeNull();
});

test('unauthenticated request to delete profile is rejected', function () {
    $this->delete('/profile', ['password' => 'Password1'])
        ->assertRedirect(route('login'));
});
