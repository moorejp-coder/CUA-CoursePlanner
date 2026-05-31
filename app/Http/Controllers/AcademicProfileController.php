<?php

namespace App\Http\Controllers;

use App\Http\Concerns\AuthorizesAccess;
use App\Models\StudentCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicProfileController extends Controller
{
    use AuthorizesAccess;

    private const ADMIT_TERMS = [
        'Fall 2020', 'Spring 2021', 'Fall 2021', 'Spring 2022', 'Fall 2022',
        'Spring 2023', 'Fall 2023', 'Spring 2024', 'Fall 2024', 'Spring 2025',
        'Fall 2025', 'Spring 2026', 'Fall 2026', 'Spring 2027',
    ];

    private const GRADUATION_TERMS = [
        'Spring 2025', 'Fall 2025', 'Spring 2026', 'Fall 2026', 'Spring 2027',
        'Fall 2027', 'Spring 2028', 'Fall 2028', 'Spring 2029', 'Fall 2029', 'Spring 2030',
    ];

    public function editAcademic(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load(['studentProfile', 'studentCourses']);
        $profile = $user->studentProfile;

        if (! $profile) {
            return Redirect::route('onboarding');
        }

        $requirements = json_decode((string) file_get_contents(storage_path('app/requirements.json')), true) ?? [];

        $specsPost = [];
        foreach ($requirements['post_2024']['specializations'] ?? [] as $key => $spec) {
            $specsPost[$key] = trim(preg_split('/\s{2,}/', $spec['label'] ?? $key)[0] ?? $key);
        }

        $specsPre = [];
        foreach ($requirements['pre_2024']['specializations'] ?? [] as $key => $spec) {
            $specsPre[$key] = trim(preg_split('/\s{2,}/', $spec['label'] ?? $key)[0] ?? $key);
        }

        $courses = $user->studentCourses;
        $catalogYear = $profile->catalog_year ?? 'post_2024';
        $isPost2024 = $catalogYear === 'post_2024';
        $selectedSpecs = array_values(array_filter([
            $profile->specialization_1,
            $profile->specialization_2,
            $profile->specialization_3,
        ]));
        $isSales = in_array('sales', $selectedSpecs, true);
        $isSingleSpec = count($selectedSpecs) <= 1;
        $allSpecs = $requirements[$catalogYear]['specializations'] ?? [];

        $otherCourses = $courses->whereNotIn('requirement_category', [
            'liberal_arts', 'business_core', 'specialization', 'accounting',
        ])->sortBy('course_code')->values();

        return view('profile.academic-edit', [
            'profile' => $profile,
            'courses' => $otherCourses,
            'specsPost' => $specsPost,
            'specsPre' => $specsPre,
            'admitTerms' => self::ADMIT_TERMS,
            'graduationTerms' => self::GRADUATION_TERMS,
            'requirements' => $requirements,
            'isPost2024' => $isPost2024,
            'isSales' => $isSales,
            'isSingleSpec' => $isSingleSpec,
            'selectedSpecs' => $selectedSpecs,
            'allSpecs' => $allSpecs,
            'laData' => $this->buildLaDataFromCourses($courses),
            'coreData' => $this->buildCoreDataFromCourses($courses),
            'specData' => $this->buildSpecDataFromCourses($courses),
            'catalogYear' => $catalogYear,
            'allowedLa' => $this->allowedLaSelects(),
        ]);
    }

    public function updateCourses(Request $request): RedirectResponse
    {
        $user = $request->user()->load(['studentProfile', 'studentCourses']);

        if (! $user->studentProfile) {
            return Redirect::route('onboarding');
        }

        $userId = $user->id;

        // ── Liberal Arts (slot-based) ─────────────────────────────────────────
        $laSlotMap = [
            'la_classical_philosophy' => 'Classical Philosophy',
            'la_modern_philosophy' => 'Modern Philosophy',
            'la_theology_1' => 'Theology I',
            'la_theology_2' => 'Theology II',
            'la_rhetoric' => 'Rhetoric & Composition',
            'la_natural_science' => 'Natural Science',
            'la_literature' => 'Literature',
            'la_fine_arts' => 'Fine Arts',
            'la_social_science' => 'Social Science',
            'la_history_politics' => 'History/Politics',
            'la_language_1' => 'Language I',
            'la_language_2' => 'Language II',
            'la_phil_elective' => 'Philosophy Elective',
            'la_theology_elective' => 'Theology Elective',
            'la_math_thinking' => 'Math Thinking',
        ];

        foreach ($laSlotMap as $field => $slotName) {
            $val = strtoupper(trim($request->input($field, '')));
            if ($val === '' || $val === 'NOT_YET') {
                StudentCourse::where('user_id', $userId)
                    ->where('requirement_category', 'liberal_arts')
                    ->where('course_name', $slotName)
                    ->delete();
            } else {
                StudentCourse::updateOrCreate(
                    ['user_id' => $userId, 'requirement_category' => 'liberal_arts', 'course_name' => $slotName],
                    ['course_code' => $val, 'status' => 'completed']
                );
            }
        }

        // ── Business Core (slot-based) ────────────────────────────────────────
        // type 'status'         → options: not_yet | in_progress | Completed; fixed course_code
        // type 'code_select'    → options: not_yet | specific_code; code = the value itself
        // type 'code_or_status' → options: not_yet | in_progress | specific_code(s)
        $coreFieldMap = [
            'core_ent118' => ['name' => 'ENT 118',                 'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_mgt123' => ['name' => 'MGT 123',                 'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_sres101' => ['name' => 'SRES 101/ECON',           'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_sres102' => ['name' => 'SRES 102/ECON',           'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_acct205' => ['name' => 'Financial Accounting',    'code' => 'ACCT 205', 'default' => 'ACCT 205', 'type' => 'status'],
            'core_acct206' => ['name' => 'Managerial Accounting',   'code' => 'ACCT 206', 'default' => 'ACCT 206', 'type' => 'status'],
            'core_fin226' => ['name' => 'Financial Management',    'code' => 'FIN 226',  'default' => 'FIN 226',  'type' => 'status'],
            'core_math' => ['name' => 'Math Requirement',        'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_bus199' => ['name' => 'Career Discernment I',    'code' => 'BUS 199',  'default' => 'BUS 199',  'type' => 'status'],
            'core_bus299a' => ['name' => 'Career Discernment II',   'code' => null,       'default' => 'BUS 299A', 'type' => 'code_or_status'],
            'core_mgt250' => ['name' => 'Business Communications', 'code' => 'MGT 250',  'default' => 'MGT 250',  'type' => 'status'],
            'core_sres290' => ['name' => 'SRES 290',                'code' => 'SRES 290', 'default' => 'SRES 290', 'type' => 'status'],
            'core_stats' => ['name' => 'Statistics',              'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_info_gateway' => ['name' => 'Info Management Gateway', 'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_mkt345' => ['name' => 'Marketing Management',    'code' => null,       'default' => 'MKT 345',  'type' => 'code_or_status'],
            'core_ethics' => ['name' => 'Business Ethics',         'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_law' => ['name' => 'Business Law',            'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_mgt365' => ['name' => 'Quantitative Methods',    'code' => null,       'default' => 'MGT 365',  'type' => 'code_or_status'],
            'core_bus399a' => ['name' => 'Career Discernment III',  'code' => null,       'default' => 'BUS 399A', 'type' => 'code_or_status'],
            'core_bus499a' => ['name' => 'Career Discernment IV',   'code' => null,       'default' => 'BUS 499A', 'type' => 'code_or_status'],
            'core_mgt475' => ['name' => 'Business Strategy',       'code' => 'MGT 475',  'default' => 'MGT 475',  'type' => 'status'],
            'core_bus498' => ['name' => 'Comprehensive Assessment', 'code' => 'BUS 498',  'default' => 'BUS 498',  'type' => 'status'],
            'core_elective_1' => ['name' => 'Business Elective 1',     'code' => null,       'default' => null,       'type' => 'code_select'],
            'core_elective_2' => ['name' => 'Business Elective 2',     'code' => null,       'default' => null,       'type' => 'code_select'],
        ];

        foreach ($coreFieldMap as $field => $meta) {
            $val = trim($request->input($field, ''));

            if ($val === '' || $val === 'not_yet') {
                StudentCourse::where('user_id', $userId)
                    ->where('requirement_category', 'business_core')
                    ->where('course_name', $meta['name'])
                    ->delete();

                continue;
            }

            if ($meta['type'] === 'status') {
                $courseCode = $meta['code'];
                $status = $val === 'in_progress' ? 'in_progress' : 'completed';
            } elseif ($meta['type'] === 'code_or_status') {
                if ($val === 'in_progress') {
                    $courseCode = $meta['default'] ?? $val;
                    $status = 'in_progress';
                } else {
                    $courseCode = strtoupper($val);
                    $status = 'completed';
                }
            } else {
                // code_select
                $courseCode = strtoupper($val);
                $status = 'completed';
            }

            if (! $courseCode) {
                continue;
            }

            StudentCourse::updateOrCreate(
                ['user_id' => $userId, 'requirement_category' => 'business_core', 'course_name' => $meta['name']],
                ['course_code' => $courseCode, 'status' => $status]
            );
        }

        // ── Specialization Courses ────────────────────────────────────────────
        $validSpecStatuses = ['completed', 'in_progress', 'not_yet'];
        foreach (array_slice((array) $request->input('spec_courses', []), 0, 50) as $rawCode => $status) {
            $code = strtoupper(trim(preg_replace('/[^A-Za-z0-9 ]/', '', (string) $rawCode)));
            $status = in_array($status, $validSpecStatuses, true) ? $status : 'not_yet';
            if (! $code) {
                continue;
            }

            if ($status === 'not_yet') {
                StudentCourse::where('user_id', $userId)
                    ->where('requirement_category', 'specialization')
                    ->where('course_code', $code)
                    ->delete();
            } else {
                StudentCourse::updateOrCreate(
                    ['user_id' => $userId, 'requirement_category' => 'specialization', 'course_code' => $code],
                    ['course_name' => $code, 'status' => $status]
                );
            }
        }

        // ── Other courses: delete only (ownership-verified) ───────────────────
        $userCourseIds = $user->studentCourses->pluck('id')->map(fn ($id) => (int) $id)->all();
        $deleteIds = array_values(array_filter(
            array_map('intval', (array) $request->input('delete_courses', [])),
            fn ($id) => in_array($id, $userCourseIds, true)
        ));
        if ($deleteIds) {
            StudentCourse::whereIn('id', $deleteIds)->where('user_id', $userId)->delete();
        }

        // ── Add a misc course ─────────────────────────────────────────────────
        $newCode = strtoupper(trim($request->input('new_course_code', '')));
        if ($newCode !== '') {
            $request->validate([
                'new_course_code' => ['required', 'string', 'max:20', 'regex:/^[A-Z]{2,6} \d{3}\w*$/'],
                'new_course_status' => ['required', Rule::in(['completed', 'in_progress', 'not_yet'])],
                'new_course_grade' => ['nullable', 'string', 'max:5'],
                'new_course_semester' => ['nullable', 'string', 'max:30'],
            ], [
                'new_course_code.regex' => 'Course code must be in the format DEPT 123 (e.g. ACCT 205, MGT 475).',
            ]);

            StudentCourse::updateOrCreate(
                ['user_id' => $userId, 'course_code' => $newCode],
                [
                    'course_name' => $newCode,
                    'requirement_category' => 'updated_by_bot',
                    'status' => $request->input('new_course_status', 'completed'),
                    'grade' => ! empty($request->input('new_course_grade')) ? trim($request->input('new_course_grade')) : null,
                    'semester_completed' => ! empty($request->input('new_course_semester')) ? trim($request->input('new_course_semester')) : null,
                ]
            );
        }

        $user->studentProfile->last_updated_at = now();
        $user->studentProfile->save();

        return Redirect::route('profile.academic.edit')->with('course_success', 'Your courses have been updated.');
    }

    public function updateAcademic(Request $request): RedirectResponse
    {
        $user = $request->user()->load('studentProfile');
        $profile = $user->studentProfile;

        if (! $profile) {
            return Redirect::route('onboarding');
        }

        $requirements = json_decode((string) file_get_contents(storage_path('app/requirements.json')), true) ?? [];
        $catalogYear = $request->input('catalog_year', $profile->catalog_year);
        $validSpecs = array_keys($requirements[$catalogYear]['specializations'] ?? []);
        $degree = $request->input('degree', $profile->degree);

        $validated = $request->validate([
            'degree' => ['required', Rule::in(['bsba', 'bs_accounting', 'double_major', 'business_minor'])],
            'catalog_year' => ['required', Rule::in(['pre_2024', 'post_2024'])],
            'admit_term' => ['required', Rule::in(self::ADMIT_TERMS)],
            'expected_graduation' => ['required', Rule::in(self::GRADUATION_TERMS)],
            'credits_completed' => ['required', 'integer', 'min:0', 'max:250'],
            'specialization_1' => [
                Rule::requiredIf($degree === 'bsba'),
                'nullable', 'string', Rule::in(array_merge([''], $validSpecs)),
            ],
            'specialization_2' => ['nullable', 'string', Rule::in(array_merge([''], $validSpecs))],
            'specialization_3' => ['nullable', 'string', Rule::in(array_merge([''], $validSpecs))],
        ]);

        if ($validated['degree'] === 'bs_accounting') {
            // Accounting has no specializations
            $validated['specialization_1'] = null;
            $validated['specialization_2'] = null;
            $validated['specialization_3'] = null;
        } elseif ($validated['degree'] === 'bsba') {
            // BSBA: apply the spec values the user submitted
            $validated['specialization_1'] = $validated['specialization_1'] ?: null;
            $validated['specialization_2'] = $validated['specialization_2'] ?: null;
            $validated['specialization_3'] = $validated['specialization_3'] ?: null;
        } else {
            // double_major and business_minor: specs are set during onboarding and
            // not shown on this form — remove them from validated so fill() leaves them intact
            unset($validated['specialization_1'], $validated['specialization_2'], $validated['specialization_3']);
        }

        // Derive standing from credits
        $credits = (int) $validated['credits_completed'];
        $validated['projected_standing'] = match (true) {
            $credits >= 90 => 'senior',
            $credits >= 60 => 'junior',
            $credits >= 30 => 'sophomore',
            default => 'freshman',
        };

        $profile->fill($validated);
        $profile->last_updated_at = now();
        $profile->save();

        return Redirect::route('profile.academic.edit')->with('success', 'Your academic profile has been updated.');
    }

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

    // ── Private helpers: build pre-population data from DB ──────────────────

    private function buildLaDataFromCourses($courses): array
    {
        $laCourses = $courses->where('requirement_category', 'liberal_arts')->keyBy('course_name');

        $slotMap = [
            'la_classical_philosophy' => 'Classical Philosophy',
            'la_modern_philosophy' => 'Modern Philosophy',
            'la_theology_1' => 'Theology I',
            'la_theology_2' => 'Theology II',
            'la_rhetoric' => 'Rhetoric & Composition',
            'la_natural_science' => 'Natural Science',
            'la_literature' => 'Literature',
            'la_fine_arts' => 'Fine Arts',
            'la_social_science' => 'Social Science',
            'la_history_politics' => 'History/Politics',
            'la_language_1' => 'Language I',
            'la_language_2' => 'Language II',
            'la_phil_elective' => 'Philosophy Elective',
            'la_theology_elective' => 'Theology Elective',
            'la_math_thinking' => 'Math Thinking',
        ];

        $data = [];
        foreach ($slotMap as $fieldKey => $slotName) {
            $course = $laCourses->get($slotName);
            $data[$fieldKey] = $course ? $course->course_code : 'not_yet';
        }

        return $data;
    }

    private function buildCoreDataFromCourses($courses): array
    {
        $core = $courses->where('requirement_category', 'business_core');
        $byCode = $core->keyBy('course_code');
        $byName = $core->keyBy('course_name');

        $statusVal = static function ($c): string {
            if (! $c) {
                return 'not_yet';
            }

            return $c->status === 'in_progress' ? 'in_progress' : 'Completed';
        };

        $codeVal = static function ($c): string {
            if (! $c) {
                return 'not_yet';
            }
            if ($c->status === 'in_progress') {
                return 'in_progress';
            }

            return $c->course_code ?: 'not_yet';
        };

        return [
            'core_ent118' => $codeVal($byName->get('ENT 118')),
            'core_mgt123' => $codeVal($byName->get('MGT 123')),
            'core_sres101' => $codeVal($byName->get('SRES 101/ECON')),
            'core_sres102' => $codeVal($byName->get('SRES 102/ECON')),
            'core_acct205' => $statusVal($byCode->get('ACCT 205')),
            'core_acct206' => $statusVal($byCode->get('ACCT 206')),
            'core_fin226' => $statusVal($byCode->get('FIN 226')),
            'core_math' => $codeVal($byName->get('Math Requirement')),
            'core_bus199' => $statusVal($byCode->get('BUS 199')),
            'core_bus299a' => $codeVal($byCode->get('BUS 299A') ?? $byCode->get('MKT 299')),
            'core_mgt250' => $statusVal($byCode->get('MGT 250')),
            'core_sres290' => $statusVal($byCode->get('SRES 290')),
            'core_stats' => $codeVal($byName->get('Statistics')),
            'core_info_gateway' => $codeVal($byName->get('Info Management Gateway')),
            'core_mkt345' => $codeVal($byCode->get('MKT 345') ?? $byCode->get('BUS 604')),
            'core_ethics' => $codeVal($byName->get('Business Ethics')),
            'core_law' => $codeVal($byName->get('Business Law')),
            'core_mgt365' => $codeVal($byCode->get('MGT 365') ?? $byCode->get('BUS 603') ?? $byName->get('Quantitative Methods')),
            'core_bus399a' => $codeVal($byCode->get('BUS 399A') ?? $byCode->get('MKT 399')),
            'core_bus499a' => $codeVal($byCode->get('BUS 499A') ?? $byCode->get('MKT 499')),
            'core_mgt475' => $statusVal($byCode->get('MGT 475')),
            'core_bus498' => $statusVal($byCode->get('BUS 498')),
            'core_elective_1' => ($c = $byName->get('Business Elective 1')) ? ($c->course_code ?? '') : '',
            'core_elective_2' => ($c = $byName->get('Business Elective 2')) ? ($c->course_code ?? '') : '',
        ];
    }

    private function buildSpecDataFromCourses($courses): array
    {
        $data = [];
        foreach ($courses->where('requirement_category', 'specialization') as $course) {
            $data[$course->course_code] = $course->status;
        }

        return $data;
    }

    private function allowedLaSelects(): array
    {
        return [
            'la_classical_philosophy' => ['not_yet', 'PHIL 201', 'PHIL 211', 'HSPH 101'],
            'la_modern_philosophy' => ['not_yet', 'PHIL 202', 'PHIL 212', 'HSPH 102'],
            'la_theology_1' => ['not_yet', 'TRS 201', 'HSTR 101'],
            'la_theology_2' => ['not_yet', 'TRS 202A', 'TRS 202B', 'HSTR (any)'],
            'la_rhetoric' => ['not_yet', 'ENG 101', 'ENG 101H', 'ENG 101C'],
            'la_natural_science' => array_merge(['not_yet'], [
                'ANTH 105', 'ANTH 108', 'ANTH 204', 'ANTH 206', 'ANTH 352', 'ANTH 354',
                'BIOL 103', 'BIOL 109', 'CHEM 10', 'CHEM 110', 'CHEM 125', 'CHEM 126',
                'CHEM 127', 'CHEM 128R', 'CHEM 130', 'PHYS 101', 'PHYS 103', 'PHYS 122',
                'PHYS 206', 'PHYS 215H', 'PSY 204', 'SAS 225', 'HSEV 101',
            ]),
            'la_literature' => array_merge(['not_yet'], [
                'ARAB 279', 'CLAS 105', 'CLAS 106', 'CLAS 211', 'CLAS 212R',
                'CLAS 244', 'CLAS 251', 'CLAS 261',
                'ENG 206', 'ENG 212', 'ENG 231', 'ENG 232', 'ENG 235', 'ENG 236',
                'ENG 278', 'ENG 305', 'ENG 306', 'ENG 312', 'ENG 341', 'ENG 345',
                'ENG 347', 'ENG 351', 'ENG 352', 'ENG 356', 'ENG 364', 'ENG 369',
                'ENG 376', 'ENG 378-R', 'ENG 379', 'ENG 461', 'ENG 462',
                'FREN 220', 'FREN 230', 'FREN 242', 'FREN 279',
                'GER 220', 'GER 225', 'GER 230', 'GER 255',
                'GS 220', 'HUM 101', 'HUM 124',
                'ITAL 212', 'ITAL 220', 'ITAL 226', 'ITAL 232',
                'MDIA 225', 'SPAN 224', 'SPAN 225', 'SPAN 240', 'SPAN 321',
                'HSHU 203', 'HSLS 353',
            ]),
            'la_fine_arts' => array_merge(['not_yet'], [
                'ARPL 211',
                'ART 201', 'ART 211', 'ART 212', 'ART 213', 'ART 222',
                'ART 251', 'ART 252', 'ART 272', 'ART 302', 'ART 308',
                'ART 317', 'ART 318', 'ART 319', 'ART 320', 'ART 335',
                'CLAS 214', 'CLAS 221', 'CLAS 251', 'CLAS 261',
                'CLAS 317', 'CLAS 318', 'CLAS 318R',
                'DR 105', 'DR 106', 'DR 110', 'DR 201', 'DR 202',
                'DR 207', 'DR 305', 'DR 403', 'DNCE 101',
                'ENG 300', 'ENG 302', 'ENGR 101', 'HIST 390A', 'ITAL 219-R',
                'MDIA 201', 'MDIA 343',
                'MUS 110', 'MUS 112', 'MUS 131', 'MUS 134', 'MUS 135',
                'MUS 178', 'MUS 276', 'MUS 304', 'MUS 327', 'MUS 328', 'MUS 328H',
                'HSLS 352', 'HSAM 101',
            ]),
            'la_social_science' => array_merge(['not_yet'], [
                'ANTH 101', 'ANTH 110', 'ANTH 201', 'ANTH 203', 'ANTH 211',
                'ANTH 226', 'ANTH 240', 'ANTH 260', 'CEE 201',
                'ECON 100', 'ECON 101', 'ECON 102', 'ECON 103', 'ECON 104', 'ECON 200',
                'GS 101', 'PSY 201', 'PSY 226', 'PSY 261',
                'SOC 101', 'SOC 102', 'SOC 102H', 'SOC 202', 'SOC 206',
                'SOC 210', 'SOC 281', 'SOC 330', 'SOC 358', 'SOC 358H',
                'SRES 101', 'SRES 102', 'SRES 345', 'SSS 101', 'SSS 226',
                'HSEV 203', 'HSSS 101', 'HSSS 102', 'HSSS 204',
            ]),
            'la_history_politics' => array_merge(['not_yet'], [
                'ANTH 215',
                'CLAS 205', 'CLAS 206', 'CLAS 206R', 'CLAS 207', 'CLAS 220',
                'CLAS 226', 'CLAS 260', 'CLAS 304', 'CLAS 308', 'ECST 315',
                'EURO 203',
                'HIST 140', 'HIST 142', 'HIST 151', 'HIST 202', 'HIST 205',
                'HIST 206', 'HIST 206R', 'HIST 208', 'HIST 222', 'HIST 224',
                'HIST 226', 'HIST 229', 'HIST 231A', 'HIST 231B', 'HIST 235',
                'HIST 246', 'HIST 257', 'HIST 258', 'HIST 301', 'HIST 308B',
                'HIST 309', 'HIST 309B', 'HIST 312', 'HIST 315', 'HIST 316',
                'HIST 334A', 'HIST 349', 'HIST 351', 'HIST 371D', 'HIST 380D',
                'HIST 384A', 'HIST 385', 'ITAL 221', 'MDIA 202',
                'POL 111', 'POL 112', 'POL 211', 'POL 226', 'WASH 101',
                'HSHU 101', 'HSHU 102', 'HSHU 204',
                'HSLS 205', 'HSLS 351', 'HSLS 354',
            ]),
            'la_language_1' => array_merge(['not_yet'], [
                'ARAB 103', 'CHN 103', 'FREN 103', 'GER 103', 'GR 103',
                'IRSH 103', 'ITAL 103', 'LAT 103', 'SPAN 103', 'SPAN 111', 'SPAN 113',
            ]),
            'la_language_2' => array_merge(['not_yet'], [
                'ARAB 104', 'CHN 104', 'FREN 104', 'GER 104', 'GR 104',
                'IRSH 104', 'ITAL 104', 'LAT 104', 'SPAN 104',
            ]),
            'la_math_thinking' => array_merge(['not_yet'], [
                'MATH 111', 'MATH 112', 'MATH 114', 'MATH 121', 'MATH 122',
                'MATH 168', 'MATH 175', 'MATH 187',
                'HSMS 230', 'HSMS 330', 'HSSS 203', 'math_exempt',
            ]),
        ];
    }

    public function suggestUpdate(Request $request): JsonResponse
    {
        $this->authorizeAccess($request, fn ($user) => $user->studentProfile !== null);

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

    public function updateField(Request $request): JsonResponse
    {
        $this->authorizeAccess($request, fn ($user) => $user->studentProfile !== null);

        $allowedFields = [
            'degree', 'catalog_year',
            'specialization_1', 'specialization_2', 'specialization_3',
            'credits_completed', 'expected_graduation', 'admit_term', 'projected_standing',
        ];

        $validated = $request->validate([
            'field' => ['required', 'string', 'in:'.implode(',', $allowedFields)],
            'value' => ['nullable', 'string', 'max:100'],
        ]);

        $field = $validated['field'];
        $value = $validated['value'] ?? null;

        $profile = $request->user()->studentProfile;

        match ($field) {
            'degree' => $this->validateEnum($value, ['bsba', 'bs_accounting', 'double_major', 'business_minor']),
            'catalog_year' => $this->validateEnum($value, ['pre_2024', 'post_2024']),
            'projected_standing' => $this->validateEnum($value, ['freshman', 'sophomore', 'junior', 'senior']),
            'credits_completed' => $this->validateCredits($value),
            'specialization_1', 'specialization_2', 'specialization_3' => $this->validateSpecialization($value, $profile),
            default => null,
        };
        $profile->$field = ($field === 'credits_completed') ? (int) $value : ($value ?: null);
        $profile->last_updated_at = now();
        $profile->save();

        return response()->json(['success' => true, 'field' => $field]);
    }

    private function validateEnum(?string $value, array $allowed): void
    {
        if (! in_array($value, $allowed, true)) {
            abort(422, 'Invalid value for field. Allowed: '.implode(', ', $allowed));
        }
    }

    private function validateCredits(?string $value): void
    {
        if (! is_numeric($value) || (int) $value < 0 || (int) $value > 250) {
            abort(422, 'credits_completed must be a number between 0 and 250.');
        }
    }

    private function validateSpecialization(?string $value, mixed $profile): void
    {
        if ($value === null) {
            return;
        }
        $requirements = json_decode((string) file_get_contents(storage_path('app/requirements.json')), true) ?? [];
        $catalogYear = $profile?->catalog_year ?? 'post_2024';
        $validSpecs = array_keys($requirements[$catalogYear]['specializations'] ?? []);
        if (! in_array($value, $validSpecs, true)) {
            abort(422, 'Invalid specialization key for '.$catalogYear.'. Valid: '.implode(', ', $validSpecs));
        }
    }

    public function dismissSemesterPrompt(Request $request): JsonResponse
    {
        $this->authorizeAccess($request, fn ($user) => $user->studentProfile !== null);

        $profile = $request->user()->studentProfile;

        if ($profile) {
            $profile->semester_prompt_shown_at = now();
            $profile->save();
        }

        return response()->json(['success' => true]);
    }
}
