<?php

use App\Services\PrerequisiteService;

beforeEach(function () {
    $this->service = new PrerequisiteService;
});

// ── checkCourse ───────────────────────────────────────────────────────────────

test('course with no prerequisites is always eligible', function () {
    $result = $this->service->checkCourse('ENT 118B', [], 'freshman', 0);

    expect($result['eligible'])->toBeTrue()
        ->and($result['missingCourses'])->toBeEmpty();
});

test('course requiring ACCT 205 is blocked without it', function () {
    $result = $this->service->checkCourse('ACCT 206', [], 'sophomore', 30);

    expect($result['eligible'])->toBeFalse()
        ->and($result['missingCourses'])->toContain('ACCT 205');
});

test('course requiring ACCT 205 is eligible when completed', function () {
    $result = $this->service->checkCourse('ACCT 206', ['ACCT 205'], 'sophomore', 30);

    expect($result['eligible'])->toBeTrue();
});

test('FIN 332 requires FIN 226 and a math course', function () {
    $result = $this->service->checkCourse('FIN 332', [], 'sophomore', 30);
    expect($result['eligible'])->toBeFalse()
        ->and($result['missingCourses'])->toHaveCount(2);

    $result = $this->service->checkCourse('FIN 332', ['FIN 226'], 'sophomore', 30);
    expect($result['eligible'])->toBeFalse()
        ->and($result['missingCourses'])->toHaveCount(1);

    $result = $this->service->checkCourse('FIN 332', ['FIN 226', 'MATH 111'], 'sophomore', 30);
    expect($result['eligible'])->toBeTrue();
});

test('OR condition in required_courses is satisfied by any alternative', function () {
    // FIN 334 requires MATH 111|112|121|122 — MATH 122 should satisfy it
    $result = $this->service->checkCourse('FIN 334', ['FIN 226', 'MATH 122'], 'sophomore', 30);
    expect($result['eligible'])->toBeTrue();
});

test('MGT 475 requires senior standing', function () {
    $completed = ['ACCT 206', 'FIN 226', 'MKT 345', 'MGT 123B'];

    $result = $this->service->checkCourse('MGT 475', $completed, 'junior', 85);
    expect($result['eligible'])->toBeFalse()
        ->and($result['missingStanding'])->toBe('senior');

    $result = $this->service->checkCourse('MGT 475', $completed, 'senior', 90);
    expect($result['eligible'])->toBeTrue();
});

test('BUS 299A requires 24 credits', function () {
    $result = $this->service->checkCourse('BUS 299A', ['BUS 199'], 'sophomore', 20);
    expect($result['eligible'])->toBeFalse()
        ->and($result['missingCredits'])->toBe(24);

    $result = $this->service->checkCourse('BUS 299A', ['BUS 199'], 'sophomore', 24);
    expect($result['eligible'])->toBeTrue();
});

test('unknown course is treated as eligible', function () {
    $result = $this->service->checkCourse('XYZ 999', [], 'freshman', 0);
    expect($result['eligible'])->toBeTrue();
});

// ── findConflicts ─────────────────────────────────────────────────────────────

test('returns empty array when no in-progress conflicts exist', function () {
    $conflicts = $this->service->findConflicts(
        ['ENT 118B'],
        [],
        'freshman',
        0
    );

    expect($conflicts)->toBeEmpty();
});

test('flags in-progress course with unmet prerequisite', function () {
    $conflicts = $this->service->findConflicts(
        ['ACCT 206'],
        [],
        'sophomore',
        30
    );

    expect($conflicts)->toHaveKey('ACCT 206')
        ->and($conflicts['ACCT 206']['missing_courses'])->toContain('ACCT 205');
});

test('does not flag in-progress course when prereqs are met', function () {
    $conflicts = $this->service->findConflicts(
        ['ACCT 206'],
        ['ACCT 205'],
        'sophomore',
        30
    );

    expect($conflicts)->toBeEmpty();
});

// ── nowEligible ───────────────────────────────────────────────────────────────

test('returns courses that are eligible and not yet taken', function () {
    $eligible = $this->service->nowEligible(
        ['ACCT 206', 'FIN 226'],
        ['ACCT 205'],
        [],
        'sophomore',
        30
    );

    expect($eligible)->toContain('ACCT 206')
        ->and($eligible)->toContain('FIN 226');
});

test('excludes already completed or in-progress courses from eligible list', function () {
    $eligible = $this->service->nowEligible(
        ['ACCT 206', 'FIN 226'],
        ['ACCT 205', 'ACCT 206'],
        ['FIN 226'],
        'sophomore',
        30
    );

    expect($eligible)->toBeEmpty();
});

test('excludes courses whose prerequisites are not yet met', function () {
    $eligible = $this->service->nowEligible(
        ['FIN 332'],
        ['FIN 226'],
        [],
        'sophomore',
        30
    );

    expect($eligible)->toBeEmpty();
});

// ── buildContextSummary ───────────────────────────────────────────────────────

test('returns a string for a brand new student', function () {
    $summary = $this->service->buildContextSummary(
        [], [], 'freshman', 0, 'bsba', null, null, null
    );

    expect($summary)->toBeString();
});

test('summary includes PREREQUISITE CONFLICTS when conflict exists', function () {
    $summary = $this->service->buildContextSummary(
        [],
        ['ACCT 206'],
        'sophomore',
        30,
        'bsba',
        null, null, null
    );

    expect($summary)->toContain('PREREQUISITE CONFLICTS')
        ->and($summary)->toContain('ACCT 206');
});

test('summary includes NOW ELIGIBLE when courses are unlocked', function () {
    $summary = $this->service->buildContextSummary(
        ['ACCT 205'],
        [],
        'sophomore',
        30,
        'bsba',
        null, null, null
    );

    expect($summary)->toContain('NOW ELIGIBLE')
        ->and($summary)->toContain('ACCT 206');
});

test('summary includes finance specialization key courses', function () {
    $summary = $this->service->buildContextSummary(
        ['FIN 226', 'MATH 111'],
        [],
        'sophomore',
        40,
        'bsba',
        'finance', null, null
    );

    // FIN 332 and/or FIN 334 should appear as now eligible
    expect($summary)->toContain('FIN 332');
});
