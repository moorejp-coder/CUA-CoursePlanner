<?php

namespace App\Services;

use App\Models\StudentCourse;
use Illuminate\Support\Collection;

// PrerequisiteService is used inside buildEligibleElectives for real-time eligibility checks

class PlannerService
{
    private array $requirements;

    public function __construct()
    {
        $this->requirements = json_decode(
            (string) file_get_contents(storage_path('app/requirements.json')),
            true
        ) ?? [];
    }

    /**
     * Build the REMAINING REQUIREMENTS context block for the chat system prompt.
     *
     * @param  Collection<int, StudentCourse>  $courses
     */
    public function buildRemainingContext(
        string $degree,
        string $catalogYear,
        string $standing,
        int $creditsCompleted,
        ?string $spec1,
        ?string $spec2,
        ?string $spec3,
        Collection $courses
    ): string {
        $completedCodes = $courses->where('status', 'completed')->pluck('course_code')->all();
        $inProgressCodes = $courses->where('status', 'in_progress')->pluck('course_code')->all();
        $takenCodes = array_merge($completedCodes, $inProgressCodes);

        $lines = ['REMAINING REQUIREMENTS:'];

        if ($degree === 'business_minor') {
            $lines = array_merge($lines, $this->remainingMinor($spec1, $takenCodes));
        } elseif ($degree === 'double_major') {
            $lines = array_merge($lines, $this->remainingDoubleMajor($spec1, $takenCodes));
        } else {
            $lines = array_merge($lines, $this->remainingCore($degree, $catalogYear, $takenCodes, $courses));
            $lines = array_merge($lines, $this->remainingSpecs($catalogYear, $takenCodes, $spec1, $spec2, $spec3));
            $lines = array_merge($lines, $this->remainingLiberalArts($courses));
            $isSales = in_array('sales', array_filter([$spec1, $spec2, $spec3]));
            $lines = array_merge($lines, $this->remainingCareerDiscernment($takenCodes, $isSales));
        }

        $creditEstimate = $this->estimateCreditsRemaining(
            $degree, $catalogYear, $creditsCompleted, $takenCodes, $courses, $spec1, $spec2, $spec3
        );
        $lines[] = "Estimated credits remaining to graduation: ~{$creditEstimate} credits";

        return implode("\n", $lines);
    }

    /**
     * Build the ELIGIBLE ELECTIVES context block.
     * Shows which elective options in each specialization the student can register for now.
     *
     * @param  string[]  $completedCodes
     * @param  string[]  $inProgressCodes
     */
    public function buildEligibleElectives(
        string $catalogYear,
        array $completedCodes,
        array $inProgressCodes,
        string $standing,
        int $credits,
        ?string $spec1,
        ?string $spec2,
        ?string $spec3
    ): string {
        $prereqService = new PrerequisiteService;
        $specData = $this->requirements[$catalogYear]['specializations'] ?? [];
        $lines = [];

        foreach (array_filter([$spec1, $spec2, $spec3]) as $specKey) {
            if (! isset($specData[$specKey])) {
                continue;
            }

            $spec = $specData[$specKey];
            $label = $this->cleanLabel($spec['label'] ?? $specKey);
            $pool = $spec['electives'] ?? [];
            $chooseCount = (int) ($spec['choose_count'] ?? 0);

            if ($chooseCount === 0 || empty($pool)) {
                continue;
            }

            $takenElectives = array_intersect($pool, array_merge($completedCodes, $inProgressCodes));
            $stillNeeded = max(0, $chooseCount - count($takenElectives));

            if ($stillNeeded === 0) {
                continue;
            }

            $eligible = $prereqService->nowEligible($pool, $completedCodes, $inProgressCodes, $standing, $credits);
            $blocked = array_values(array_diff($pool, array_merge($completedCodes, $inProgressCodes, $eligible)));

            $parts = [];
            if ($eligible) {
                $parts[] = 'can register now: '.implode(', ', $eligible);
            }
            if ($blocked) {
                $parts[] = 'prereqs not yet met: '.implode(', ', $blocked);
            }

            if ($parts) {
                $lines[] = "ELECTIVE OPTIONS - {$label} (need {$stillNeeded} more): ".implode(' | ', $parts);
            }
        }

        return $lines ? implode("\n", $lines) : '';
    }

    /**
     * Build the FASTEST PATH context block.
     * Identifies critical sequential chains, semester bottlenecks, and minimum semesters remaining.
     *
     * @param  string[]  $completedCodes
     * @param  string[]  $inProgressCodes
     */
    public function buildFastestPathAnalysis(
        string $degree,
        string $catalogYear,
        array $completedCodes,
        array $inProgressCodes,
        string $standing,
        int $creditsCompleted,
        ?string $spec1,
        ?string $spec2,
        ?string $spec3
    ): string {
        $taken = array_merge($completedCodes, $inProgressCodes);
        $lines = [];

        // Critical sequential chains — each step must be completed before the next
        // Format: [label, [[course, semester_lock], ...]]
        // semester_lock: 'Fall', 'Spring', or null
        $chains = [
            'Core gateway' => [
                ['ACCT 205', null],
                ['ACCT 206 + FIN 226', null],
                ['MKT 345 (junior req)', null],
                ['MGT 475 + BUS 498 (senior req)', null],
            ],
            'Finance chain' => [
                ['ACCT 205', null],
                ['FIN 226 + MATH 111', null],
                ['FIN 334', 'Fall'],
                ['FIN 332', 'Spring'],
                ['FIN 436', null],
            ],
            'Accounting chain' => [
                ['ACCT 205', null],
                ['ACCT 310', 'Fall'],
                ['ACCT 311', 'Spring'],
                ['ACCT 312 + ACCT 412', 'Spring'],
                ['ACCT 418 + ACCT 419', null],
            ],
            'Marketing chain' => [
                ['ACCT 205', null],
                ['MKT 345 (junior req)', null],
                ['MKT 348 or MKT 346', null],
                ['MKT 457', null],
            ],
            'Sales chain' => [
                ['MKT 349 (junior req)', null],
                ['MKT 422 + MKT 435 (senior req)', null],
            ],
        ];

        // Determine which chains are relevant to this student
        $specData = $this->requirements[$catalogYear]['specializations'] ?? [];
        $relevantChainKeys = ['Core gateway'];
        foreach (array_filter([$spec1, $spec2, $spec3]) as $sk) {
            if (str_contains($sk, 'finance')) {
                $relevantChainKeys[] = 'Finance chain';
            }
            if (str_contains($sk, 'accounting')) {
                $relevantChainKeys[] = 'Accounting chain';
            }
            if (str_contains($sk, 'marketing')) {
                $relevantChainKeys[] = 'Marketing chain';
            }
            if (str_contains($sk, 'sales')) {
                $relevantChainKeys[] = 'Sales chain';
            }
        }
        if ($degree === 'bs_accounting') {
            $relevantChainKeys[] = 'Accounting chain';
        }
        $relevantChainKeys = array_unique($relevantChainKeys);

        $maxChainSemesters = 0;
        $semesterBottlenecks = [];

        foreach ($relevantChainKeys as $chainKey) {
            $chain = $chains[$chainKey] ?? [];
            $remainingSteps = [];
            $chainSemesters = 0;

            foreach ($chain as [$stepLabel, $lock]) {
                // Extract representative course codes from the step label
                preg_match_all('/[A-Z]{2,5} \d{3}\w*/', $stepLabel, $m);
                $codes = $m[0];

                // Step is done if ALL representative codes are in taken (or step has no parseable codes)
                $stepDone = ! empty($codes) && count(array_diff($codes, $taken)) === 0;

                if (! $stepDone) {
                    $remainingSteps[] = $stepLabel.($lock ? " ({$lock} only)" : '');
                    $chainSemesters++;
                    if ($lock) {
                        $semesterBottlenecks[] = "{$stepLabel} ({$lock} only)";
                    }
                }
            }

            if (! empty($remainingSteps)) {
                $lines[] = "Critical chain - {$chainKey}: ".implode(' -> ', $remainingSteps).". Min {$chainSemesters} semester(s) remaining in this chain.";
                $maxChainSemesters = max($maxChainSemesters, $chainSemesters);
            }
        }

        // Semester bottlenecks summary (deduplicated)
        $semesterBottlenecks = array_unique($semesterBottlenecks);
        if ($semesterBottlenecks) {
            $lines[] = 'Semester locks in remaining path: '.implode(', ', $semesterBottlenecks);
        }

        // Minimum semesters estimate: max of credit-based and chain-based
        $creditEstimate = $this->estimateCreditsRemaining(
            $degree, $catalogYear, $creditsCompleted,
            $taken, collect([]), $spec1, $spec2, $spec3
        );
        $creditBasedSemesters = (int) ceil($creditEstimate / 15);
        $minSemesters = max($creditBasedSemesters, $maxChainSemesters);

        if ($minSemesters > 0) {
            $lines[] = "Minimum semesters to graduation: ~{$minSemesters} (based on credits + critical chains).";
        }

        return $lines ? implode("\n", $lines) : '';
    }

    /** @return string[] */
    private function remainingCore(string $degree, string $catalogYear, array $takenCodes, Collection $courses): array
    {
        $isPost2024 = $catalogYear === 'post_2024';
        $isAccounting = $degree === 'bs_accounting';

        // Groups: label => [acceptable codes that satisfy the slot]
        $groups = [
            'Vocation of Business' => ['ENT 118A', 'ENT 118B'],
            'Foundations of Business' => ['MGT 123A', 'MGT 123B'],
            'Econ I (SRES 101)' => ['SRES 101', 'SRES 101H', 'ECON 100', 'ECON 102', 'ECON 104'],
            'Econ II (SRES 102)' => ['SRES 102', 'SRES 102H', 'ECON 101', 'ECON 103', 'ECON 200'],
            'Financial Accounting (ACCT 205)' => ['ACCT 205'],
            'Managerial Accounting (ACCT 206)' => ['ACCT 206'],
            'Introduction to Finance (FIN 226)' => ['FIN 226'],
            'Marketing Management (MKT 345)' => ['MKT 345', 'BUS 604'],
            'Business Ethics' => ['MGT 301', 'ACCT 442'],
            'Business Law' => ['MGT 321', 'MGT 322', 'MGT 371', 'SRES 411', 'ACCT 480'],
            'Business Communications (MGT 250)' => ['MGT 250'],
            'Business Strategy (MGT 475)' => ['MGT 475'],
            'Comprehensive Assessment (BUS 498)' => ['BUS 498', 'ACCT 498'],
            'Math Requirement' => ['MATH 110', 'MATH 111'],
        ];

        if ($isPost2024) {
            $groups['Applied Economics (SRES 290)'] = ['SRES 290'];
            $groups['Business Analytics (MGT 265)'] = ['MGT 265', 'ECON 223'];
            $groups['Info Management Gateway'] = ['MGT 240', 'MGT 351', 'MGT 361', 'ECON 370', 'FIN 313', 'EAM 406', 'ENT 519', 'ACCT 425', 'DA 124', 'MGT 331'];
        } else {
            $groups['Statistics (MGT 365)'] = ['MGT 365', 'ECON 223', 'MGT 265'];
            $groups['Info Management Gateway'] = ['MGT 240', 'MGT 351', 'MGT 361', 'ECON 370', 'FIN 313', 'EAM 406'];
        }

        if ($isAccounting) {
            unset($groups['Business Strategy (MGT 475)'], $groups['Comprehensive Assessment (BUS 498)']);
            $groups['BS Accounting Comprehensive (ACCT 498)'] = ['ACCT 498'];
        }

        $remaining = [];
        $done = 0;

        foreach ($groups as $label => $codes) {
            if (count(array_intersect($codes, $takenCodes)) > 0) {
                $done++;
            } else {
                $remaining[] = $label;
            }
        }

        $total = count($groups);
        $prefix = "Business Core ({$done} of {$total} done)";

        if (empty($remaining)) {
            return ["{$prefix}: All core requirements complete"];
        }

        return ["{$prefix} — still needed: ".implode(', ', $remaining)];
    }

    /** @return string[] */
    private function remainingSpecs(string $catalogYear, array $takenCodes, ?string ...$specKeys): array
    {
        $lines = [];
        $specData = $this->requirements[$catalogYear]['specializations'] ?? [];

        foreach ($specKeys as $specKey) {
            if (! $specKey || ! isset($specData[$specKey])) {
                continue;
            }

            $spec = $specData[$specKey];
            $label = $this->cleanLabel($spec['label'] ?? $specKey);
            $required = $spec['required'] ?? [];
            $electives = $spec['electives'] ?? [];
            $chooseCount = (int) ($spec['choose_count'] ?? 0);

            $remainingRequired = array_values(array_diff($required, $takenCodes));
            $takenElectives = array_values(array_intersect($electives, $takenCodes));
            $electivesStillNeeded = max(0, $chooseCount - count($takenElectives));

            if (empty($remainingRequired) && $electivesStillNeeded === 0) {
                $lines[] = "Specialization - {$label}: Complete";

                continue;
            }

            $parts = [];
            if ($remainingRequired) {
                $parts[] = 'required: '.implode(', ', $remainingRequired);
            }
            if ($electivesStillNeeded > 0) {
                $availableElectives = array_values(array_diff($electives, $takenCodes));
                $parts[] = "need {$electivesStillNeeded} elective(s) from: ".implode(', ', $availableElectives);
            }

            $lines[] = "Specialization - {$label}: ".implode('; ', $parts);
        }

        return $lines;
    }

    /** @return string[] */
    private function remainingLiberalArts(Collection $courses): array
    {
        $laSlots = [
            'Classical Philosophy', 'Modern Philosophy', 'Theology I', 'Theology II',
            'Rhetoric & Composition', 'Natural Science', 'Literature', 'Fine Arts',
            'History/Politics', 'Language I', 'Language II',
            'Philosophy Elective', 'Theology Elective', 'Social Science', 'Math Thinking',
        ];

        $laCourses = $courses->where('requirement_category', 'liberal_arts');
        $filledNames = $laCourses->pluck('course_name')->all();

        // SPAN 111 or SPAN 113 satisfies both Language I and Language II in one course.
        $span11xExempt = $laCourses
            ->whereIn('course_code', ['SPAN 111', 'SPAN 113'])
            ->where('course_name', 'Language I')
            ->isNotEmpty();

        $remaining = array_values(array_filter(
            $laSlots,
            function (string $slot) use ($filledNames, $span11xExempt): bool {
                if ($slot === 'Language II' && $span11xExempt) {
                    return false;
                }

                return ! in_array($slot, $filledNames);
            }
        ));

        $done = count($laSlots) - count($remaining);

        if (empty($remaining)) {
            return ['Liberal Arts (15 of 15): All liberal arts requirements complete'];
        }

        return ["Liberal Arts ({$done} of 15 done) — unfilled slots: ".implode(', ', $remaining)];
    }

    /** @return string[] */
    private function remainingCareerDiscernment(array $takenCodes, bool $isSales = false): array
    {
        $slots = [
            'BUS 199' => ['BUS 199'],
            ($isSales ? 'MKT 299' : 'BUS 299A') => ['BUS 299A', 'MKT 299'],
            ($isSales ? 'MKT 399' : 'BUS 399A') => ['BUS 399A', 'MKT 399'],
            ($isSales ? 'MKT 499' : 'BUS 499A') => ['BUS 499A', 'MKT 499'],
        ];

        $remaining = [];
        foreach ($slots as $label => $codes) {
            if (count(array_intersect($codes, $takenCodes)) === 0) {
                $remaining[] = $label;
            }
        }

        if (empty($remaining)) {
            return ['Career Discernment (4 of 4): Complete'];
        }

        return ['Career Discernment — still needed: '.implode(', ', $remaining).' (1 credit each)'];
    }

    /** @return string[] */
    private function remainingMinor(?string $minorKey, array $takenCodes): array
    {
        if (! $minorKey) {
            return ['Business Minor: No minor selected'];
        }

        $minorData = $this->requirements['business_minors'][$minorKey] ?? null;
        if (! $minorData) {
            return ["Business Minor ({$minorKey}): Data not found"];
        }

        $label = $minorData['label'] ?? $minorKey;
        $required = array_column($minorData['required'] ?? [], 'options');
        $required = array_map(fn ($opts) => $opts[0] ?? '?', $required);
        $remaining = array_values(array_diff($required, $takenCodes));

        $lines = [];
        if (empty($remaining)) {
            $lines[] = "Business Minor - {$label}: Required courses complete";
        } else {
            $lines[] = "Business Minor - {$label} — required still needed: ".implode(', ', $remaining);
        }

        if (! empty($minorData['electives'])) {
            foreach ($minorData['electives'] as $group) {
                $groupOpts = $group['options'] ?? [];
                $taken = array_intersect($groupOpts, $takenCodes);
                $need = (int) ($group['choose'] ?? 1) - count($taken);
                if ($need > 0) {
                    $avail = array_values(array_diff($groupOpts, $takenCodes));
                    $lines[] = "  Elective: need {$need} from: ".implode(', ', $avail);
                }
            }
        }

        return $lines;
    }

    /** @return string[] */
    private function remainingDoubleMajor(?string $pairKey, array $takenCodes): array
    {
        $pairData = $this->requirements['double_major']['pairs'][$pairKey] ?? null;
        $core = $this->requirements['double_major']['business_core'] ?? [];

        $remainingCore = array_values(array_filter(
            $core,
            function (string $item) use ($takenCodes) {
                preg_match('/^([A-Z]+ \d+\w*)/', $item, $m);

                return isset($m[1]) && ! in_array($m[1], $takenCodes);
            }
        ));

        $lines = [];

        if (empty($remainingCore)) {
            $lines[] = 'Double Major Core: All 8 core courses complete';
        } else {
            $lines[] = 'Double Major Core — still needed: '.implode(', ', array_map(function (string $item) {
                preg_match('/^([A-Z]+ \d+\w*)/', $item, $m);

                return $m[1] ?? $item;
            }, $remainingCore));
        }

        if ($pairData) {
            $pairLabel = $pairData['label'] ?? $pairKey;
            $pairCourses = $pairData['courses'] ?? [];
            $remainingPair = array_values(array_diff($pairCourses, $takenCodes));
            if (empty($remainingPair)) {
                $lines[] = "Pair ({$pairLabel}): Both courses complete";
            } else {
                $lines[] = "Pair ({$pairLabel}) — still needed: ".implode(', ', $remainingPair);
            }
        }

        return $lines;
    }

    /** Strip warning annotations (e.g. "Finance  ⚠ Requires MATH 111" → "Finance") */
    private function cleanLabel(string $raw): string
    {
        // Split on two or more spaces, which precede the ⚠ warning annotation
        return trim(preg_split('/\s{2,}/', $raw)[0] ?? $raw);
    }

    private function estimateCreditsRemaining(
        string $degree,
        string $catalogYear,
        int $creditsCompleted,
        array $takenCodes,
        Collection $courses,
        ?string $spec1,
        ?string $spec2,
        ?string $spec3
    ): int {
        // Business minor: count remaining required + elective courses × 3 credits
        if ($degree === 'business_minor') {
            $minorKey = $spec1;
            $minorData = $this->requirements['business_minors'][$minorKey] ?? null;
            if (! $minorData) {
                return max(0, 18 - $creditsCompleted);
            }
            $required = array_column($minorData['required'] ?? [], 'options');
            $required = array_map(fn ($opts) => $opts[0] ?? '?', $required);
            $remainingReq = count(array_diff($required, $takenCodes));
            $electiveCredits = 0;
            foreach ($minorData['electives'] ?? [] as $group) {
                $taken = count(array_intersect($group['options'] ?? [], $takenCodes));
                $need = max(0, (int) ($group['choose'] ?? 1) - $taken);
                $electiveCredits += $need * 3;
            }

            return ($remainingReq * 3) + $electiveCredits;
        }

        // Double major: remaining core + pair courses × 3 credits each
        if ($degree === 'double_major') {
            $core = $this->requirements['double_major']['business_core'] ?? [];
            $remainingCore = array_filter($core, function (string $item) use ($takenCodes) {
                preg_match('/^([A-Z]+ \d+\w*)/', $item, $m);

                return isset($m[1]) && ! in_array($m[1], $takenCodes);
            });
            $pairCourses = $this->requirements['double_major']['pairs'][$spec1]['courses'] ?? [];
            $remainingPair = array_diff($pairCourses, $takenCodes);

            return (count($remainingCore) + count($remainingPair)) * 3;
        }

        // BSBA / BS Accounting: count remaining required courses and multiply by their credit value
        $credits = 0;

        // Core slots (most are 3 credits; career discernment = 1 each; BUS 498/ACCT 498 = 0)
        $zeroCredit = ['BUS 498', 'ACCT 498'];
        $oneCredit = ['BUS 199', 'BUS 299A', 'MKT 299', 'BUS 399A', 'MKT 399', 'BUS 499A', 'MKT 499'];

        $coreGroups = [
            ['ENT 118A', 'ENT 118B'],
            ['MGT 123A', 'MGT 123B'],
            ['SRES 101', 'SRES 101H', 'ECON 100', 'ECON 102', 'ECON 104'],
            ['SRES 102', 'SRES 102H', 'ECON 101', 'ECON 103', 'ECON 200'],
            ['ACCT 205'],
            ['ACCT 206'],
            ['FIN 226'],
            ['MKT 345', 'BUS 604'],
            ['MGT 301', 'ACCT 442'],
            ['MGT 321', 'MGT 322', 'MGT 371', 'SRES 411', 'ACCT 480'],
            ['MGT 250'],
            ['MGT 475'],
            ['BUS 498', 'ACCT 498'],
            ['MATH 110', 'MATH 111'],
            $catalogYear === 'post_2024'
                ? ['SRES 290']
                : [],
            $catalogYear === 'post_2024'
                ? ['MGT 265', 'ECON 223']
                : ['MGT 365', 'ECON 223', 'MGT 265'],
            ['MGT 240', 'MGT 351', 'MGT 361', 'ECON 370', 'FIN 313', 'EAM 406', 'ENT 519', 'ACCT 425', 'DA 124', 'MGT 331'],
        ];

        foreach ($coreGroups as $group) {
            if (empty($group)) {
                continue;
            }
            if (count(array_intersect($group, $takenCodes)) === 0) {
                // Not yet satisfied — determine credit value from representative code
                $rep = $group[0];
                if (in_array($rep, $zeroCredit)) {
                    $credits += 0;
                } elseif (array_intersect($group, $oneCredit)) {
                    $credits += 1;
                } else {
                    $credits += 3;
                }
            }
        }

        // Career discernment (1 credit each)
        $careerSlots = [
            ['BUS 199'],
            ['BUS 299A', 'MKT 299'],
            ['BUS 399A', 'MKT 399'],
            ['BUS 499A', 'MKT 499'],
        ];
        foreach ($careerSlots as $slot) {
            if (count(array_intersect($slot, $takenCodes)) === 0) {
                $credits += 1;
            }
        }

        // Liberal arts: each unfilled slot = 3 credits
        $laSlots = [
            'Classical Philosophy', 'Modern Philosophy', 'Theology I', 'Theology II',
            'Rhetoric & Composition', 'Natural Science', 'Literature', 'Fine Arts',
            'History/Politics', 'Language I', 'Language II',
            'Philosophy Elective', 'Theology Elective', 'Social Science', 'Math Thinking',
        ];
        $laCourses = $courses->where('requirement_category', 'liberal_arts');
        $filledLa = $laCourses->pluck('course_name')->all();
        // SPAN 111 or SPAN 113 covers both Language I and Language II.
        if ($laCourses->whereIn('course_code', ['SPAN 111', 'SPAN 113'])->where('course_name', 'Language I')->isNotEmpty()) {
            $filledLa = array_unique(array_merge($filledLa, ['Language II']));
        }
        $remainingLa = count(array_diff($laSlots, $filledLa));
        $credits += $remainingLa * 3;

        // Specialization required + electives (3 credits each)
        $specData = $this->requirements[$catalogYear]['specializations'] ?? [];
        foreach ([$spec1, $spec2, $spec3] as $specKey) {
            if (! $specKey || ! isset($specData[$specKey])) {
                continue;
            }
            $spec = $specData[$specKey];
            $required = $spec['required'] ?? [];
            $electives = $spec['electives'] ?? [];
            $chooseCount = (int) ($spec['choose_count'] ?? 0);
            $remainingRequired = count(array_diff($required, $takenCodes));
            $takenElectives = count(array_intersect($electives, $takenCodes));
            $remainingElectives = max(0, $chooseCount - $takenElectives);
            $credits += ($remainingRequired + $remainingElectives) * 3;
        }

        // BS Accounting additional courses
        if ($degree === 'bs_accounting') {
            $acctRequired = ['ACCT 310', 'ACCT 311', 'ACCT 312', 'ACCT 412', 'ACCT 417', 'ACCT 418', 'ACCT 419', 'ACCT 422'];
            foreach ($acctRequired as $code) {
                if (! in_array($code, $takenCodes)) {
                    $credits += 3;
                }
            }
        }

        // 2 business electives (can't know if taken without a marker — include if student has few electives)
        $electiveCodes = $courses->where('requirement_category', 'business_core')
            ->whereIn('course_name', ['Business Elective 1', 'Business Elective 2'])
            ->count();
        $credits += max(0, 2 - $electiveCodes) * 3;

        return max(0, $credits);
    }
}
