<?php

namespace App\Http\Controllers;

use App\Models\StudentCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['studentProfile', 'studentCourses']);
        $profile = $user->studentProfile;
        $courses = $user->studentCourses;

        if (! $profile) {
            return view('profile.academic', [
                'profile' => null,
                'laRows' => [],
                'coreRows' => [],
                'specBlocks' => [],
                'transferCourses' => collect(),
                'otherCourses' => collect(),
                'summaries' => [],
            ]);
        }

        $isPost2024 = $profile->catalog_year === 'post_2024';
        $isAccounting = $profile->degree === 'bs_accounting';
        $isSingleSpec = empty($profile->specialization_2) && empty($profile->specialization_3);
        $coursesByCode = $courses->keyBy('course_code');
        $coursesByName = $courses->where('requirement_category', 'liberal_arts')->keyBy('course_name');

        // ── Liberal Arts rows (15 canonical slots) ──────────────────────────
        $laSlots = [
            'Classical Philosophy', 'Modern Philosophy',
            'Theology I', 'Theology II',
            'Rhetoric & Composition', 'Natural Science',
            'Literature', 'Fine Arts',
            'Social Science', 'History/Politics',
            'Language I', 'Language II',
            'Philosophy Elective', 'Theology Elective',
            'Math Thinking',
        ];
        $laRows = [];
        foreach ($laSlots as $slotName) {
            $course = $coursesByName->get($slotName);
            $laRows[] = [
                'slot_name' => $slotName,
                'course_code' => $course?->course_code ?? '—',
                'status' => $course?->status ?? 'not_yet',
                'semester' => $course?->semester_completed ?? null,
            ];
        }

        // ── Business Core rows ───────────────────────────────────────────────
        $coreSlots = $this->buildCoreSlots($isPost2024, $isSingleSpec);
        $coreRows = [];
        foreach ($coreSlots as $slot) {
            $dbCourse = null;
            if ($slot['code']) {
                $dbCourse = $coursesByCode->get($slot['code'])
                    ?? $courses->where('requirement_category', 'business_core')
                        ->where('course_name', $slot['name'])
                        ->first();
            } else {
                $dbCourse = $courses->where('requirement_category', 'business_core')
                    ->where('course_name', $slot['name'])
                    ->first();
            }
            $coreRows[] = [
                'slot_name' => $slot['name'],
                'course_code' => $dbCourse?->course_code ?? ($slot['code'] ?: '—'),
                'status' => $dbCourse?->status ?? 'not_yet',
                'semester' => $dbCourse?->semester_completed ?? null,
            ];
        }

        // ── Specialization blocks (BSBA only) ─────────────────────────────────
        $specBlocks = [];
        $acctRows = [];
        $acctSummary = ['completed' => 0, 'total' => 0];

        if ($isAccounting) {
            $acctSlots = $this->buildAccountingSlots($isPost2024);
            $acctCourses = $courses->where('requirement_category', 'accounting')->keyBy('course_code');
            foreach ($acctSlots as $slot) {
                $course = $acctCourses->get($slot['code']);
                $acctRows[] = [
                    'course_code' => $slot['code'],
                    'course_name' => $slot['name'],
                    'prereq' => $slot['pre'],
                    'type' => $slot['type'],
                    'status' => $course?->status ?? 'not_yet',
                    'semester' => $course?->semester_completed ?? null,
                ];
            }
            $acctSummary = [
                'completed' => collect($acctRows)->where('status', 'completed')->count(),
                'total' => count($acctRows),
            ];
        } else {
            $specsJson = json_decode(file_get_contents(storage_path('app/specializations.json')), true);
            $catalogKey = $isPost2024 ? 'post_2024' : 'pre_2024';
            $allSpecs = $specsJson[$catalogKey]['specializations'] ?? [];
            $specKeys = array_filter([
                $profile->specialization_1,
                $profile->specialization_2,
                $profile->specialization_3,
            ]);
            $specCourses = $courses->where('requirement_category', 'specialization');

            foreach ($specKeys as $specKey) {
                $specData = $allSpecs[$specKey] ?? null;
                if (! $specData) {
                    continue;
                }
                $required = $specData['required'] ?? [];
                $electives = $specData['electives'] ?? [];
                $chooseCount = $specData['choose_count'] ?? 0;
                $rows = [];
                foreach ($required as $code) {
                    $course = $specCourses->where('course_code', $code)->first();
                    $rows[] = [
                        'course_code' => $code,
                        'type' => 'Required',
                        'status' => $course?->status ?? 'not_yet',
                        'semester' => $course?->semester_completed ?? null,
                    ];
                }
                foreach ($electives as $code) {
                    $course = $specCourses->where('course_code', $code)->first();
                    $rows[] = [
                        'course_code' => $code,
                        'type' => 'Elective',
                        'status' => $course?->status ?? 'not_yet',
                        'semester' => $course?->semester_completed ?? null,
                    ];
                }
                $total = count($required) + $chooseCount;
                $specBlocks[] = [
                    'name' => $specData['name'],
                    'rows' => $rows,
                    'completed' => collect($rows)->where('status', 'completed')->count(),
                    'in_progress' => collect($rows)->where('status', 'in_progress')->count(),
                    'total_required' => $total,
                    'choose_count' => $chooseCount,
                    'required_count' => count($required),
                ];
            }
        }

        // ── Summaries ─────────────────────────────────────────────────────────
        $laCompleted = collect($laRows)->where('status', 'completed')->count();
        $coreCompleted = collect($coreRows)->where('status', 'completed')->count();
        $summaries = [
            'la' => ['completed' => $laCompleted, 'total' => count($laRows)],
            'core' => ['completed' => $coreCompleted, 'total' => count($coreRows)],
        ];

        // ── Transfer and other courses ────────────────────────────────────────
        $transferCourses = $courses->where('requirement_category', 'transfer_credit');
        $otherCourses = $courses->whereNotIn('requirement_category', [
            'liberal_arts', 'business_core', 'specialization', 'accounting',
            'in_progress', 'transfer_credit',
        ]);

        return view('profile.academic', compact(
            'profile', 'laRows', 'coreRows', 'specBlocks',
            'transferCourses', 'otherCourses', 'summaries',
            'isAccounting', 'acctRows', 'acctSummary'
        ));
    }

    private function buildAccountingSlots(bool $isPost2024): array
    {
        $slots = [
            ['code' => 'ACCT 310', 'name' => 'Intermediate Accounting I',        'pre' => 'ACCT 206', 'type' => 'Required'],
            ['code' => 'ACCT 311', 'name' => 'Intermediate Accounting II',       'pre' => 'ACCT 310', 'type' => 'Required'],
        ];

        if (! $isPost2024) {
            $slots[] = ['code' => 'ACCT 312', 'name' => 'Intermediate Accounting III', 'pre' => 'ACCT 311', 'type' => 'Required'];
        }

        return array_merge($slots, [
            ['code' => 'ACCT 315', 'name' => 'Cost Accounting',                  'pre' => 'ACCT 206',  'type' => 'Required'],
            ['code' => 'ACCT 412', 'name' => 'Auditing',                         'pre' => 'ACCT 311',  'type' => 'Required'],
            ['code' => 'ACCT 417', 'name' => 'Government and Non-Profit Acct.',  'pre' => null,        'type' => 'Required'],
            ['code' => 'ACCT 418', 'name' => 'Advanced Accounting',              'pre' => 'ACCT 311',  'type' => 'Required'],
            ['code' => 'ACCT 419', 'name' => 'Federal Income Taxation',          'pre' => null,        'type' => 'Required'],
            ['code' => 'ACCT 422', 'name' => 'Accounting Analytics',             'pre' => 'ACCT 311',  'type' => 'Required'],
            ['code' => 'ACCT 442', 'name' => 'Accounting Ethics',                'pre' => null,        'type' => 'Required'],
            ['code' => 'FIN 332',  'name' => 'Corporate Finance',                'pre' => 'FIN 226',   'type' => 'Required'],
            ['code' => 'FIN 334',  'name' => 'Investments',                      'pre' => 'FIN 332',   'type' => 'Required'],
            ['code' => 'ACCT 480', 'name' => 'Accounting Elective (ACCT 480)',   'pre' => null,        'type' => 'Elective'],
            ['code' => 'ACCT 491', 'name' => 'Accounting Elective (ACCT 491)',   'pre' => null,        'type' => 'Elective'],
            ['code' => 'ECON 370', 'name' => 'Accounting Elective (ECON 370)',   'pre' => null,        'type' => 'Elective'],
        ]);
    }

    private function buildCoreSlots(bool $isPost2024, bool $isSingleSpec): array
    {
        $slots = [
            ['name' => 'ENT 118 — Vocation of Business',       'code' => ''],
            ['name' => 'MGT 123 — Foundations of Business',    'code' => ''],
            ['name' => 'SRES 101 — Markets & Prosperity I',     'code' => ''],
            ['name' => 'SRES 102 — Markets & Prosperity II',    'code' => ''],
            ['name' => 'Financial Accounting',                  'code' => 'ACCT 205'],
            ['name' => 'Managerial Accounting',                 'code' => 'ACCT 206'],
            ['name' => 'Financial Management',                  'code' => 'FIN 226'],
            ['name' => 'Math Requirement',                      'code' => ''],
            ['name' => 'Career Discernment I',                  'code' => 'BUS 199'],
            ['name' => 'Career Discernment II',                 'code' => 'BUS 299A'],
            ['name' => 'Business Communications',               'code' => 'MGT 250'],
        ];

        if ($isPost2024) {
            $slots[] = ['name' => 'SRES 290', 'code' => 'SRES 290'];
        }

        $slots = array_merge($slots, [
            ['name' => 'Statistics',                            'code' => ''],
            ['name' => 'Info Management Gateway',               'code' => ''],
            ['name' => 'Marketing Management',                  'code' => 'MKT 345'],
            ['name' => 'Business Ethics',                       'code' => ''],
            ['name' => 'Business Law',                          'code' => ''],
        ]);

        if (! $isPost2024) {
            $slots[] = ['name' => 'Quantitative Methods', 'code' => ''];
        }

        $slots = array_merge($slots, [
            ['name' => 'Career Discernment III',                'code' => 'BUS 399A'],
            ['name' => 'Career Discernment IV',                 'code' => 'BUS 499A'],
            ['name' => 'Business Strategy',                     'code' => 'MGT 475'],
            ['name' => 'Comprehensive Assessment',              'code' => 'BUS 498'],
        ]);

        if (! $isPost2024 && $isSingleSpec) {
            $slots[] = ['name' => 'Business Elective 1', 'code' => ''];
            $slots[] = ['name' => 'Business Elective 2', 'code' => ''];
        }

        return $slots;
    }

    public function suggestUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_code' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:completed,in_progress,not_yet'],
            'grade' => ['nullable', 'string', 'max:5'],
            'semester' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user();

        $course = StudentCourse::firstOrNew([
            'user_id' => $user->id,
            'course_code' => strtoupper($validated['course_code']),
        ]);

        $course->status = $validated['status'];
        $course->grade = $validated['grade'] ?? null;
        $course->semester_completed = $validated['semester'] ?? null;

        if (! $course->exists) {
            $course->course_name = strtoupper($validated['course_code']);
            $course->requirement_category = 'updated_by_bot';
        }

        $course->save();

        if ($user->studentProfile) {
            $user->studentProfile->last_updated_at = now();
            $user->studentProfile->save();
        }

        return response()->json(['success' => true, 'course_code' => $course->course_code]);
    }

    public function dismissSemesterPrompt(Request $request): JsonResponse
    {
        $profile = $request->user()->studentProfile;

        if ($profile) {
            $profile->semester_prompt_shown_at = now();
            $profile->save();
        }

        return response()->json(['success' => true]);
    }
}
