<?php

namespace App\Services;

use App\Models\StudentCourse;
use Illuminate\Support\Collection;

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
            $lines = array_merge($lines, $this->remainingCareerDiscernment($takenCodes));
        }

        $creditEstimate = $this->estimateCreditsRemaining($degree, $creditsCompleted);
        $lines[] = "Estimated credits remaining to graduation: ~{$creditEstimate} credits";

        return implode("\n", $lines);
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
            $label = $spec['label'] ?? $specKey;
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

        $filledNames = $courses
            ->where('requirement_category', 'liberal_arts')
            ->pluck('course_name')
            ->all();

        $remaining = array_values(array_filter(
            $laSlots,
            fn ($slot) => ! in_array($slot, $filledNames)
        ));

        $done = count($laSlots) - count($remaining);

        if (empty($remaining)) {
            return ['Liberal Arts (15 of 15): All liberal arts requirements complete'];
        }

        return ["Liberal Arts ({$done} of 15 done) — unfilled slots: ".implode(', ', $remaining)];
    }

    /** @return string[] */
    private function remainingCareerDiscernment(array $takenCodes): array
    {
        $slots = [
            'BUS 199' => ['BUS 199'],
            'BUS 299A' => ['BUS 299A', 'MKT 299'],
            'BUS 399A' => ['BUS 399A', 'MKT 399'],
            'BUS 499A' => ['BUS 499A', 'MKT 499'],
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

    private function estimateCreditsRemaining(string $degree, int $creditsCompleted): int
    {
        $totalRequired = match ($degree) {
            'bs_accounting' => 120,
            'double_major' => 120,
            'business_minor' => 18,
            default => 120,
        };

        return max(0, $totalRequired - $creditsCompleted);
    }
}
