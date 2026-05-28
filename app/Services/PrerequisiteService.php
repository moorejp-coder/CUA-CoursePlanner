<?php

namespace App\Services;

class PrerequisiteService
{
    /** @var array<string, array<string, mixed>> */
    private array $prereqs;

    private const STANDING_ORDER = [
        'freshman' => 0,
        'sophomore' => 1,
        'junior' => 2,
        'senior' => 3,
    ];

    public function __construct()
    {
        $raw = json_decode((string) file_get_contents(storage_path('app/requirements.json')), true) ?? [];
        $this->prereqs = $raw['prerequisites'] ?? [];
    }

    /**
     * Check whether a student meets all prerequisites for a given course.
     *
     * @param  string[]  $completedCodes  Courses with status = 'completed'
     * @return array{eligible: bool, missing_courses: string[], missing_standing: string|null, missing_credits: int|null}
     */
    public function checkCourse(
        string $courseCode,
        array $completedCodes,
        string $standing,
        int $credits
    ): array {
        $prereq = $this->prereqs[$courseCode] ?? null;

        if ($prereq === null) {
            return ['eligible' => true, 'missing_courses' => [], 'missing_standing' => null, 'missing_credits' => null];
        }

        $missingCourses = [];
        $missingStanding = null;
        $missingCredits = null;

        foreach ($prereq['required_courses'] ?? [] as $req) {
            if (! $this->meetsCourseReq($req, $completedCodes)) {
                $missingCourses[] = str_replace('|', ' or ', $req);
            }
        }

        $requiredStanding = $prereq['required_standing'] ?? null;
        if ($requiredStanding !== null && ! $this->standingMet($requiredStanding, $standing)) {
            $missingStanding = $requiredStanding;
        }

        $requiredCredits = $prereq['required_credits'] ?? null;
        if ($requiredCredits !== null && $credits < $requiredCredits) {
            $missingCredits = $requiredCredits;
        }

        $eligible = empty($missingCourses) && $missingStanding === null && $missingCredits === null;

        return compact('eligible', 'missingCourses', 'missingStanding', 'missingCredits');
    }

    /**
     * Find any in-progress courses whose prerequisites were not met at enrollment time.
     *
     * @param  string[]  $inProgressCodes
     * @param  string[]  $completedCodes
     * @return array<string, array{missing_courses: string[], missing_standing: string|null, missing_credits: int|null}>
     */
    public function findConflicts(
        array $inProgressCodes,
        array $completedCodes,
        string $standing,
        int $credits
    ): array {
        $conflicts = [];

        foreach ($inProgressCodes as $code) {
            $result = $this->checkCourse($code, $completedCodes, $standing, $credits);
            if (! $result['eligible']) {
                $conflicts[$code] = [
                    'missing_courses' => $result['missingCourses'],
                    'missing_standing' => $result['missingStanding'],
                    'missing_credits' => $result['missingCredits'],
                ];
            }
        }

        return $conflicts;
    }

    /**
     * From a list of candidate course codes, return those the student is now eligible for
     * but has not yet completed or started.
     *
     * @param  string[]  $candidateCodes
     * @param  string[]  $completedCodes
     * @param  string[]  $inProgressCodes
     * @return string[]
     */
    public function nowEligible(
        array $candidateCodes,
        array $completedCodes,
        array $inProgressCodes,
        string $standing,
        int $credits
    ): array {
        $taken = array_merge($completedCodes, $inProgressCodes);
        $eligible = [];

        foreach ($candidateCodes as $code) {
            if (in_array($code, $taken)) {
                continue;
            }
            $result = $this->checkCourse($code, $completedCodes, $standing, $credits);
            if ($result['eligible']) {
                $eligible[] = $code;
            }
        }

        return $eligible;
    }

    /**
     * Build a concise prerequisite-status summary string for the chat context.
     *
     * @param  string[]  $completedCodes
     * @param  string[]  $inProgressCodes
     */
    public function buildContextSummary(
        array $completedCodes,
        array $inProgressCodes,
        string $standing,
        int $credits,
        string $degree,
        ?string $specialization1,
        ?string $specialization2,
        ?string $specialization3
    ): string {
        $lines = [];

        // Prerequisite conflicts in currently in-progress courses
        $conflicts = $this->findConflicts($inProgressCodes, $completedCodes, $standing, $credits);
        if ($conflicts) {
            $parts = [];
            foreach ($conflicts as $code => $info) {
                $reasons = [];
                if ($info['missing_courses']) {
                    $reasons[] = 'missing: '.implode(', ', $info['missing_courses']);
                }
                if ($info['missing_standing']) {
                    $reasons[] = 'needs '.$info['missing_standing'].' standing';
                }
                if ($info['missing_credits']) {
                    $reasons[] = 'needs '.$info['missing_credits'].' credits';
                }
                $parts[] = $code.' ('.implode('; ', $reasons).')';
            }
            $lines[] = 'PREREQUISITE CONFLICTS (in-progress courses with unmet prereqs): '.implode(' | ', $parts);
        }

        // Key courses the student is now newly eligible to take
        $keyCourses = $this->keyCourses($degree, $specialization1, $specialization2, $specialization3);
        $eligible = $this->nowEligible($keyCourses, $completedCodes, $inProgressCodes, $standing, $credits);
        if ($eligible) {
            $lines[] = 'NOW ELIGIBLE (key required courses student can take now): '.implode(', ', $eligible);
        }

        // Key courses still blocked and why (top blockers only)
        $blocked = [];
        foreach ($keyCourses as $code) {
            if (in_array($code, array_merge($completedCodes, $inProgressCodes))) {
                continue;
            }
            $result = $this->checkCourse($code, $completedCodes, $standing, $credits);
            if (! $result['eligible']) {
                $reasons = [];
                if ($result['missingCourses']) {
                    $reasons[] = implode(', ', $result['missingCourses']);
                }
                if ($result['missingStanding']) {
                    $reasons[] = 'needs '.$result['missingStanding'].' standing';
                }
                if ($result['missingCredits']) {
                    $reasons[] = 'needs '.$result['missingCredits'].' credits';
                }
                $blocked[$code] = implode('; ', $reasons);
            }
        }
        if ($blocked) {
            $parts = array_map(fn ($code, $reason) => "{$code} ({$reason})", array_keys($blocked), $blocked);
            $lines[] = 'STILL BLOCKED (key required courses not yet eligible): '.implode(' | ', $parts);
        }

        return $lines ? "\n".implode("\n", $lines) : '';
    }

    private function meetsCourseReq(string $req, array $completedCodes): bool
    {
        // Skip non-course tokens (e.g. 'math_placement_1_or_2')
        foreach (explode('|', $req) as $alt) {
            $alt = trim($alt);
            if (! str_contains($alt, '_') && in_array($alt, $completedCodes)) {
                return true;
            }
            // Accept math-placement tokens as always-satisfied (we can't verify placement in profile)
            if (str_contains($alt, 'math_placement') || str_contains($alt, 'placement')) {
                return true;
            }
        }

        return false;
    }

    private function standingMet(string $required, string $actual): bool
    {
        return (self::STANDING_ORDER[$actual] ?? 0) >= (self::STANDING_ORDER[$required] ?? 0);
    }

    /**
     * Returns the set of key required courses relevant to a degree + specialization.
     *
     * @return string[]
     */
    private function keyCourses(
        string $degree,
        ?string $spec1,
        ?string $spec2,
        ?string $spec3
    ): array {
        $isSales = in_array('sales', array_filter([$spec1, $spec2, $spec3]));

        // Universal core gates that matter to almost every student
        $core = [
            'MGT 123B', 'ACCT 205', 'ACCT 206', 'FIN 226', 'MKT 345',
            'MGT 301', 'MGT 250', 'MGT 265', 'MGT 475', 'BUS 498',
            'BUS 199',
            $isSales ? 'MKT 299' : 'BUS 299A',
            $isSales ? 'MKT 399' : 'BUS 399A',
            $isSales ? 'MKT 499' : 'BUS 499A',
        ];

        if ($degree === 'double_major') {
            $core = ['MGT 123B', 'ACCT 205', 'FIN 226', 'MKT 345', 'MGT 301', 'MGT 265', 'MGT 475', 'BUS 498'];
        }

        if ($degree === 'business_minor') {
            $core = ['MGT 123B', 'ACCT 205'];
        }

        if ($degree === 'bs_accounting') {
            $core = array_merge($core, ['ACCT 310', 'ACCT 311', 'ACCT 312', 'ACCT 412', 'ACCT 418', 'ACCT 419', 'ACCT 422', 'MATH 111']);
        }

        // Specialization-specific key courses
        $specMap = [
            'finance' => ['MATH 111', 'FIN 332', 'FIN 334', 'FIN 436'],
            'marketing_management' => ['MKT 346', 'MKT 348', 'MKT 457'],
            'sales' => ['MKT 349', 'MKT 422', 'MKT 435'],
            'entrepreneurship' => ['ENT 372', 'ENT 472', 'ENT 455'],
            'international_business' => ['MGT 390', 'MKT 362', 'MGT 389'],
            'hr_management' => ['MGT 311', 'MGT 330', 'MGT 410'],
            'operations_management' => ['MGT 311', 'MGT 312', 'MGT 332', 'MGT 347'],
            'technology_management' => ['MGT 240', 'MGT 347', 'MGT 332', 'MGT 351'],
            'markets_political_economy' => ['SRES 315', 'SRES 325', 'SRES 345', 'SRES 470'],
            'sports_management' => ['MGT 311', 'MGT 324', 'MGT 327', 'MKT 325'],
            'data_analytics' => ['MGT 265', 'MGT 331', 'DA 124'],
        ];

        foreach ([$spec1, $spec2, $spec3] as $spec) {
            if ($spec && isset($specMap[$spec])) {
                $core = array_unique(array_merge($core, $specMap[$spec]));
            }
        }

        return $core;
    }
}
