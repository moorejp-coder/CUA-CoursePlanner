<?php

use App\Models\StudentCourse;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/** Create a minimal StudentProfile for a user (no factory exists). */
function makeProfile(User $user, array $attrs = []): StudentProfile
{
    return StudentProfile::create(array_merge([
        'user_id' => $user->id,
        'full_name' => $user->name,
        'degree' => 'bsba',
        'catalog_year' => 'post_2024',
        'admit_term' => 'Fall 2025',
        'expected_graduation' => 'Spring 2028',
        'credits_completed' => 30,
        'projected_standing' => 'Sophomore',
        'last_updated_at' => now(),
    ], $attrs));
}

// ── Helpers ───────────────────────────────────────────────────────────────────

/** Minimal valid Step-3 payload (all fields nullable). */
function step3Payload(array $overrides = []): array
{
    return array_merge([
        'la_classical_philosophy' => 'not_yet',
        'la_modern_philosophy' => 'not_yet',
        'la_theology_1' => 'not_yet',
        'la_theology_2' => 'not_yet',
        'la_rhetoric' => 'not_yet',
        'la_natural_science' => 'not_yet',
        'la_literature' => 'not_yet',
        'la_fine_arts' => 'not_yet',
        'la_social_science' => 'not_yet',
        'la_history_politics' => 'not_yet',
        'la_language_1' => 'not_yet',
        'la_language_2' => 'not_yet',
        'la_math_thinking' => 'not_yet',
        'la_phil_elective' => '',
        'la_theology_elective' => '',
        'la_social_science_autofilled' => '0',
    ], $overrides);
}

/** Minimal valid Step-4 payload with a few completed courses. */
function step4Payload(array $overrides = []): array
{
    return array_merge([
        'core_ent118' => 'ENT 118B',
        'core_mgt123' => 'MGT 123B',
        'core_sres101' => 'SRES 101',
        'core_sres102' => 'not_yet',
        'core_sres290' => 'not_yet',
        'core_acct205' => 'Completed',
        'core_acct206' => 'not_yet',
        'core_fin226' => 'not_yet',
        'core_mgt250' => 'not_yet',
        'core_mgt365' => 'not_yet',
        'core_mgt475' => 'not_yet',
        'core_bus498' => 'not_yet',
        'core_bus199' => 'Completed',
        'core_bus299a' => 'not_yet',
        'core_bus399a' => 'not_yet',
        'core_bus499a' => 'not_yet',
        'core_mkt345' => 'not_yet',
        'core_stats' => 'not_yet',
        'core_math' => 'MATH 111',
        'core_info_gateway' => 'not_yet',
        'core_ethics' => 'not_yet',
        'core_law' => 'not_yet',
        'core_elective_1' => '',
        'core_elective_2' => '',
        'transfers' => [],
    ], $overrides);
}

// ── 1. Registration ───────────────────────────────────────────────────────────

test('new user registration redirects to onboarding', function () {
    Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);

    $this->post('/register', [
        'name' => 'Jane Cardinal',
        'email' => 'jcardinal@cua.edu',
        'password' => 'correct-horse-battery-staple',
        'password_confirmation' => 'correct-horse-battery-staple',
    ])->assertRedirect(route('onboarding'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'jcardinal@cua.edu']);
});

test('non-CUA email is rejected at registration', function () {
    Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);

    $this->post('/register', [
        'name' => 'Outsider',
        'email' => 'person@gmail.com',
        'password' => 'correct-horse-battery-staple',
        'password_confirmation' => 'correct-horse-battery-staple',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

// ── 2. Onboarding access guards ───────────────────────────────────────────────

test('unauthenticated user cannot access onboarding', function () {
    $this->get(route('onboarding'))->assertRedirect('/login');
});

test('user with existing profile is redirected to chat', function () {
    $user = User::factory()->create();
    makeProfile($user);

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertRedirect(route('chat'));
});

// ── 3. BSBA full onboarding wizard ───────────────────────────────────────────

test('BSBA student completes all 6 steps and profile is saved to database', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Step 1 — basic info; Fall 2025 → post_2024 catalog year
    $this->post(route('onboarding.save', 1), [
        'full_name' => 'Jane Cardinal',
        'admit_term' => 'Fall 2025',
        'degree' => 'bsba',
        'expected_graduation' => 'Spring 2028',
    ])->assertRedirect(route('onboarding.step', 2));

    // Step 2 — pick Finance specialization
    $this->post(route('onboarding.save', 2), [
        'specialization_1' => 'finance',
        'specialization_2' => '',
        'specialization_3' => '',
    ])->assertRedirect(route('onboarding.step', 3));

    // Step 3 — liberal arts; mark two slots completed to exercise course saving
    $this->post(route('onboarding.save', 3), step3Payload([
        'la_classical_philosophy' => 'PHIL 201',
        'la_rhetoric' => 'ENG 101',
    ]))->assertRedirect(route('onboarding.step', 4));

    // Step 4 — business core with several completions
    $this->post(route('onboarding.save', 4), step4Payload())
        ->assertRedirect(route('onboarding.step', 5));

    // Step 5 — Finance spec courses
    $this->post(route('onboarding.save', 5), [
        'spec_courses' => [
            'FIN 226' => 'completed',
            'FIN 334' => 'in_progress',
            'FIN 332' => 'not_yet',
            'FIN 436' => 'not_yet',
            'FIN 450' => 'not_yet',
        ],
    ])->assertRedirect(route('onboarding.step', 6));

    // Step 6 — credits; triggers profile save and redirects to chat
    $this->post(route('onboarding.save', 6), [
        'credits_completed' => 45,
        'in_progress_courses' => ['ACCT 206', 'FIN 334'],
    ])->assertRedirect(route('chat'));

    // Profile row was created with correct values
    $profile = StudentProfile::where('user_id', $user->id)->first();
    expect($profile)->not->toBeNull()
        ->and($profile->degree)->toBe('bsba')
        ->and($profile->catalog_year)->toBe('post_2024')
        ->and($profile->specialization_1)->toBe('finance')
        ->and($profile->credits_completed)->toBe(45)
        ->and($profile->projected_standing)->toBe('Sophomore');

    // LA course rows saved correctly
    $courses = StudentCourse::where('user_id', $user->id)->get();
    expect($courses)->not->toBeEmpty();

    $phil = $courses->where('course_name', 'Classical Philosophy')->first();
    expect($phil)->not->toBeNull()
        ->and($phil->course_code)->toBe('PHIL 201')
        ->and($phil->status)->toBe('completed')
        ->and($phil->requirement_category)->toBe('liberal_arts');

    // Finance course saved correctly
    $fin226 = $courses->where('course_code', 'FIN 226')->first();
    expect($fin226)->not->toBeNull()
        ->and($fin226->status)->toBe('completed');
});

// ── 4. Degree-path routing ────────────────────────────────────────────────────

test('BS Accounting step 1 skips to step 3 and step 4 routes to accounting step', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('onboarding.save', 1), [
        'full_name' => 'Audit Student',
        'admit_term' => 'Fall 2023',
        'degree' => 'bs_accounting',
        'expected_graduation' => 'Spring 2027',
    ])->assertRedirect(route('onboarding.step', 3)); // step 2 skipped

    $this->post(route('onboarding.save', 3), step3Payload());
    $this->post(route('onboarding.save', 4), step4Payload())
        ->assertRedirect(route('onboarding.step.accounting')); // routes to accounting step
});

test('Business Minor step 2 skips directly to step 5', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('onboarding.save', 1), [
        'full_name' => 'Minor Student',
        'admit_term' => 'Fall 2024',
        'degree' => 'business_minor',
        'expected_graduation' => 'Spring 2027',
    ])->assertRedirect(route('onboarding.step', 2));

    $this->post(route('onboarding.save', 2), [
        'business_minor' => 'entrepreneurship',
    ])->assertRedirect(route('onboarding.step', 5)); // skips steps 3 and 4
});

test('Double Major step 2 skips to step 4 and step 4 skips to step 6', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('onboarding.save', 1), [
        'full_name' => 'Double Student',
        'admit_term' => 'Fall 2024',
        'degree' => 'double_major',
        'expected_graduation' => 'Spring 2028',
    ])->assertRedirect(route('onboarding.step', 2));

    $this->post(route('onboarding.save', 2), [
        'double_major_pair' => 'pair_a',
        'spec_courses' => [],
    ])->assertRedirect(route('onboarding.step', 4)); // skips step 3 (LA)

    $this->post(route('onboarding.save', 4), step4Payload())
        ->assertRedirect(route('onboarding.step', 6)); // skips step 5 (spec courses)
});

// ── 5. Chat ───────────────────────────────────────────────────────────────────

test('chat page loads for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('chat'))
        ->assertOk()
        ->assertViewIs('chat');
});

test('bot responds to a chat message with the AI reply', function () {
    Http::fake([
        'https://api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => ['content' => 'For Finance you need FIN 226, FIN 334, and FIN 332.'],
                'finish_reason' => 'stop',
            ]],
            'model' => 'llama-3.3-70b-versatile',
            'usage' => ['prompt_tokens' => 500, 'completion_tokens' => 80, 'total_tokens' => 580],
        ], 200),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('chat.message'), [
            'message' => 'What courses do I need for Finance?',
            'history' => [],
        ])
        ->assertOk()
        ->assertJsonStructure(['message'])
        ->assertJsonPath('message', 'For Finance you need FIN 226, FIN 334, and FIN 332.');
});

test('chat returns 422 when message field is missing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('chat.message'), ['history' => []])
        ->assertStatus(422);
});

test('chat returns 502 when Groq API fails', function () {
    Http::fake([
        'https://api.groq.com/*' => Http::response(['error' => 'Service Unavailable'], 503),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('chat.message'), [
            'message' => 'Help me plan my courses.',
            'history' => [],
        ])
        ->assertStatus(502)
        ->assertJsonStructure(['error']);
});

// ── 6. Bot-driven profile update ──────────────────────────────────────────────

test('student can accept a bot-suggested course completion', function () {
    $user = User::factory()->create();
    makeProfile($user);

    $this->actingAs($user)
        ->postJson(route('profile.suggest-update'), [
            'course_code' => 'ACCT 205',
            'status' => 'completed',
            'grade' => 'A',
            'semester' => 'Fall 2024',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('course_code', 'ACCT 205');

    $this->assertDatabaseHas('student_courses', [
        'user_id' => $user->id,
        'course_code' => 'ACCT 205',
        'status' => 'completed',
        'semester_completed' => 'Fall 2024',
    ]);
});

test('profile update requires an existing student profile', function () {
    $user = User::factory()->create(); // no profile

    $this->actingAs($user)
        ->postJson(route('profile.suggest-update'), [
            'course_code' => 'ACCT 205',
            'status' => 'completed',
        ])
        ->assertStatus(403); // AuthorizesAccess returns 403 when profile check fails
});

test('profile update rejects invalid status values', function () {
    $user = User::factory()->create();
    makeProfile($user);

    $this->actingAs($user)
        ->postJson(route('profile.suggest-update'), [
            'course_code' => 'ACCT 205',
            'status' => 'definitely_passing_this',
        ])
        ->assertStatus(422);
});

test('updating a course twice overwrites the previous status', function () {
    $user = User::factory()->create();
    makeProfile($user);

    $this->actingAs($user)
        ->postJson(route('profile.suggest-update'), ['course_code' => 'FIN 226', 'status' => 'in_progress']);

    $this->actingAs($user)
        ->postJson(route('profile.suggest-update'), ['course_code' => 'FIN 226', 'status' => 'completed']);

    expect(StudentCourse::where('user_id', $user->id)->where('course_code', 'FIN 226')->count())->toBe(1);
    $this->assertDatabaseHas('student_courses', [
        'user_id' => $user->id,
        'course_code' => 'FIN 226',
        'status' => 'completed',
    ]);
});

// ── 7. Full end-to-end journey ────────────────────────────────────────────────

test('complete journey: register -> onboard -> chat -> accept profile update', function () {
    Http::fake([
        'https://api.pwnedpasswords.com/*' => Http::response('', 200),
        'https://api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => ['content' => 'Here is a 4-year plan for your Marketing degree...'],
                'finish_reason' => 'stop',
            ]],
            'model' => 'llama-3.3-70b-versatile',
            'usage' => ['total_tokens' => 200],
        ], 200),
    ]);

    // Register
    $this->post('/register', [
        'name' => 'Full Journey Student',
        'email' => 'fjs@cua.edu',
        'password' => 'correct-horse-battery-staple',
        'password_confirmation' => 'correct-horse-battery-staple',
    ])->assertRedirect(route('onboarding'));

    $user = User::where('email', 'fjs@cua.edu')->firstOrFail();
    // Verify email and re-bind auth so the verified middleware sees the fresh model
    $user->markEmailAsVerified();
    $this->actingAs($user->fresh());

    // Complete all 6 onboarding steps
    $this->post(route('onboarding.save', 1), [
        'full_name' => 'Full Journey Student', 'admit_term' => 'Fall 2025',
        'degree' => 'bsba', 'expected_graduation' => 'Spring 2028',
    ]);
    $this->post(route('onboarding.save', 2), [
        'specialization_1' => 'marketing', 'specialization_2' => '', 'specialization_3' => '',
    ]);
    $this->post(route('onboarding.save', 3), step3Payload());
    $this->post(route('onboarding.save', 4), step4Payload());
    $this->post(route('onboarding.save', 5), [
        'spec_courses' => ['MKT 345' => 'completed', 'MKT 348' => 'not_yet', 'MKT 457' => 'not_yet'],
    ]);
    $this->post(route('onboarding.save', 6), [
        'credits_completed' => 30, 'in_progress_courses' => [],
    ])->assertRedirect(route('chat'));

    expect(StudentProfile::where('user_id', $user->id)->exists())->toBeTrue();

    // Chat responds with the mocked AI reply
    $this->actingAs($user)
        ->postJson(route('chat.message'), ['message' => 'Build me a 4-year plan.', 'history' => []])
        ->assertOk()
        ->assertJsonStructure(['message']);

    // Accept a bot-suggested profile update
    $this->postJson(route('profile.suggest-update'), [
        'course_code' => 'MKT 345',
        'status' => 'completed',
        'semester' => 'Fall 2024',
    ])->assertOk()->assertJsonPath('success', true);

    $this->assertDatabaseHas('student_courses', [
        'user_id' => $user->id,
        'course_code' => 'MKT 345',
        'status' => 'completed',
    ]);
});
