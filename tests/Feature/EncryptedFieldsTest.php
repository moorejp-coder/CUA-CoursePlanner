<?php

use App\Models\StudentCourse;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// ── StudentProfile: FERPA-protected fields are encrypted at rest ──────────────

test('StudentProfile sensitive fields are stored as ciphertext in the database', function () {
    $user = User::factory()->create();

    StudentProfile::create([
        'user_id' => $user->id,
        'full_name' => 'Jane Doe',
        'degree' => 'bsba',
        'catalog_year' => 'post_2024',
        'admit_term' => 'Fall 2023',
        'expected_graduation' => 'Spring 2027',
        'gpa' => '3.85',
        'math_placement' => 'Calculus-ready',
        'language_placement' => 'Spanish III',
        'credits_completed' => 60,
        'projected_standing' => 'Junior',
    ]);

    // Read the raw DB row — bypasses the Eloquent encrypted cast
    $raw = DB::table('student_profiles')->where('user_id', $user->id)->first();

    expect($raw->full_name)->not->toBe('Jane Doe');
    expect($raw->gpa)->not->toBe('3.85');
    expect($raw->math_placement)->not->toBe('Calculus-ready');
    expect($raw->language_placement)->not->toBe('Spanish III');

    // Raw values must be ciphertext, not readable plaintext
    expect($raw->full_name)->toStartWith('eyJ');  // base64-encoded JSON ciphertext prefix
    expect($raw->gpa)->toStartWith('eyJ');
});

test('StudentProfile sensitive fields decrypt transparently through the model', function () {
    $user = User::factory()->create();

    StudentProfile::create([
        'user_id' => $user->id,
        'full_name' => 'Jane Doe',
        'degree' => 'bsba',
        'catalog_year' => 'post_2024',
        'admit_term' => 'Fall 2023',
        'expected_graduation' => 'Spring 2027',
        'gpa' => '3.85',
        'math_placement' => 'Calculus-ready',
        'language_placement' => 'Spanish III',
        'credits_completed' => 60,
        'projected_standing' => 'Junior',
    ]);

    $profile = StudentProfile::where('user_id', $user->id)->first();

    expect($profile->full_name)->toBe('Jane Doe');
    expect($profile->gpa)->toBe('3.85');
    expect($profile->math_placement)->toBe('Calculus-ready');
    expect($profile->language_placement)->toBe('Spanish III');
});

test('nullable encrypted fields store and retrieve null correctly', function () {
    $user = User::factory()->create();

    StudentProfile::create([
        'user_id' => $user->id,
        'full_name' => 'Test Student',
        'degree' => 'bsba',
        'catalog_year' => 'post_2024',
        'admit_term' => 'Fall 2024',
        'expected_graduation' => 'Spring 2028',
        'gpa' => null,
        'math_placement' => null,
        'language_placement' => null,
        'credits_completed' => 0,
    ]);

    $profile = StudentProfile::where('user_id', $user->id)->first();

    expect($profile->gpa)->toBeNull();
    expect($profile->math_placement)->toBeNull();
    expect($profile->language_placement)->toBeNull();
});

// ── StudentCourse: grade and notes are encrypted at rest ──────────────────────

test('StudentCourse grade and notes are stored as ciphertext in the database', function () {
    $user = User::factory()->create();

    StudentCourse::create([
        'user_id' => $user->id,
        'course_code' => 'ACCT 205',
        'course_name' => 'Financial Accounting',
        'requirement_category' => 'business_core',
        'status' => 'completed',
        'grade' => 'A-',
        'semester_completed' => 'Fall 2023',
        'notes' => 'Great professor, exam was tough.',
    ]);

    $raw = DB::table('student_courses')->where('user_id', $user->id)->first();

    expect($raw->grade)->not->toBe('A-');
    expect($raw->notes)->not->toBe('Great professor, exam was tough.');
    expect($raw->grade)->toStartWith('eyJ');
    expect($raw->notes)->toStartWith('eyJ');

    // Non-encrypted fields are still readable in raw SQL
    expect($raw->course_code)->toBe('ACCT 205');
    expect($raw->status)->toBe('completed');
});

test('StudentCourse grade and notes decrypt transparently through the model', function () {
    $user = User::factory()->create();

    StudentCourse::create([
        'user_id' => $user->id,
        'course_code' => 'ACCT 205',
        'course_name' => 'Financial Accounting',
        'requirement_category' => 'business_core',
        'status' => 'completed',
        'grade' => 'A-',
        'semester_completed' => 'Fall 2023',
        'notes' => 'Great professor, exam was tough.',
    ]);

    $course = StudentCourse::where('user_id', $user->id)->first();

    expect($course->grade)->toBe('A-');
    expect($course->notes)->toBe('Great professor, exam was tough.');
});

test('StudentCourse nullable grade and notes store and retrieve null correctly', function () {
    $user = User::factory()->create();

    StudentCourse::create([
        'user_id' => $user->id,
        'course_code' => 'MGT 475',
        'course_name' => 'Business Strategy',
        'requirement_category' => 'business_core',
        'status' => 'in_progress',
        'grade' => null,
        'notes' => null,
    ]);

    $course = StudentCourse::where('user_id', $user->id)->first();

    expect($course->grade)->toBeNull();
    expect($course->notes)->toBeNull();
});
