<?php

namespace App\Http\Controllers;

use App\Models\StudentCourse;
use App\Models\StudentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    private const TOTAL_STEPS = 6;

    public function index(): RedirectResponse
    {
        // If user already has a profile, skip to chat
        if (Auth::user()->studentProfile) {
            return redirect()->route('chat');
        }

        return redirect()->route('onboarding.step', 1);
    }

    public function show(int $step): View|RedirectResponse
    {
        if (Auth::user()->studentProfile) {
            return redirect()->route('chat');
        }

        if ($step < 1 || $step > self::TOTAL_STEPS) {
            return redirect()->route('onboarding.step', 1);
        }

        $data = session('onboarding', []);
        $isAccounting = ($data['degree'] ?? '') === 'bs_accounting';

        // BS Accounting skips step 2 (Specializations)
        if ($isAccounting && $step === 2) {
            return redirect()->route('onboarding.step', 3);
        }

        // BS Accounting skips step 5 (Spec Courses) — redirect to the accounting step
        if ($isAccounting && $step === 5) {
            return redirect()->route('onboarding.step.accounting');
        }

        $requirements = json_decode(Storage::get('requirements.json'), true) ?? [];
        [$wizardStep, $wizardTotal] = $this->computeWizardProgress($step, $isAccounting);

        return view("onboarding.step{$step}", [
            'step' => $step,
            'totalSteps' => self::TOTAL_STEPS,
            'wizardStep' => $wizardStep,
            'wizardTotal' => $wizardTotal,
            'degree' => $data['degree'] ?? 'bsba',
            'data' => $data,
            'requirements' => $requirements,
            'socialScienceAutoFill' => $step === 3 ? $this->computeSocialScienceAutoFill($data) : null,
        ]);
    }

    public function showAccounting(): View|RedirectResponse
    {
        if (Auth::user()->studentProfile) {
            return redirect()->route('chat');
        }

        $data = session('onboarding', []);

        if (($data['degree'] ?? '') !== 'bs_accounting') {
            return redirect()->route('onboarding.step', 5);
        }

        $requirements = json_decode(Storage::get('requirements.json'), true) ?? [];

        return view('onboarding.step_accounting', [
            'step' => 'accounting',
            'totalSteps' => self::TOTAL_STEPS,
            'wizardStep' => 4,
            'wizardTotal' => 5,
            'degree' => 'bs_accounting',
            'data' => $data,
            'requirements' => $requirements,
        ]);
    }

    public function saveAccounting(Request $request): RedirectResponse
    {
        if (Auth::user()->studentProfile) {
            return redirect()->route('chat');
        }

        $data = session('onboarding', []);

        if (($data['degree'] ?? '') !== 'bs_accounting') {
            return redirect()->route('onboarding.step', 5);
        }

        $data = array_merge($data, $this->validateAccounting($request));
        session(['onboarding' => $data]);

        return redirect()->route('onboarding.step', 6);
    }

    public function save(Request $request, int $step): RedirectResponse
    {
        if (Auth::user()->studentProfile) {
            return redirect()->route('chat');
        }

        $data = session('onboarding', []);

        match ($step) {
            1 => $data = array_merge($data, $this->validateStep1($request)),
            2 => $data = array_merge($data, $this->validateStep2($request, $data)),
            3 => $data = array_merge($data, $this->validateStep3($request)),
            4 => $data = array_merge($data, $this->validateStep4($request)),
            5 => $data = array_merge($data, $this->validateStep5($request)),
            6 => $data = array_merge($data, $this->validateStep6($request)),
            default => null,
        };

        session(['onboarding' => $data]);

        if ($step === self::TOTAL_STEPS) {
            $this->saveProfile($data);
            session()->forget('onboarding');

            return redirect()->route('chat')->with('onboarding_complete', true);
        }

        $isAccounting = ($data['degree'] ?? '') === 'bs_accounting';

        // BS Accounting skips step 2 (Specializations) and step 5 (Spec Courses)
        if ($isAccounting && $step === 1) {
            return redirect()->route('onboarding.step', 3);
        }

        if ($isAccounting && $step === 4) {
            return redirect()->route('onboarding.step.accounting');
        }

        return redirect()->route('onboarding.step', $step + 1);
    }

    private function validateStep1(Request $request): array
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'admit_term' => ['required', 'string'],
            'degree' => ['required', 'in:bsba,bs_accounting'],
            'expected_graduation' => ['required', 'string'],
        ]);

        // Auto-determine catalog year from admit term
        $catalogYear = $this->determineCatalogYear($validated['admit_term']);
        $validated['catalog_year'] = $catalogYear;

        return $validated;
    }

    private function validateStep2(Request $request, array $existingData): array
    {
        $validated = $request->validate([
            'specialization_1' => ['required', 'string'],
            'specialization_2' => ['nullable', 'string'],
            'specialization_3' => ['nullable', 'string'],
        ]);

        return $validated;
    }

    private function validateStep3(Request $request): array
    {
        $request->validate([
            'la_phil_elective' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $v = strtoupper(trim((string) $value));
                    if (! $v) {
                        return;
                    }
                    if (! str_starts_with($v, 'PHIL ') && $v !== 'HSPH 203' && $v !== 'HSPH 204') {
                        $fail('Philosophy Elective must be a PHIL course (e.g. PHIL 301), HSPH 203, or HSPH 204.');
                    }
                },
            ],
            'la_theology_elective' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $v = strtoupper(trim((string) $value));
                    if (! $v) {
                        return;
                    }
                    if (! str_starts_with($v, 'TRS ') && ! str_starts_with($v, 'HSTR ') && $v !== 'HSEV 102') {
                        $fail('Theology Elective must be a TRS course, any HSTR course, or HSEV 102.');
                    }
                },
            ],
        ]);

        $fields = [
            'la_classical_philosophy', 'la_modern_philosophy',
            'la_theology_1', 'la_theology_2',
            'la_rhetoric', 'la_natural_science',
            'la_literature', 'la_fine_arts',
            'la_social_science', 'la_history_politics',
            'la_language_1', 'la_language_2',
            'la_phil_elective', 'la_theology_elective',
            'la_math_thinking',
        ];

        $data = [];
        foreach ($fields as $field) {
            $raw = $request->input($field, '');
            if (in_array($field, ['la_phil_elective', 'la_theology_elective'])) {
                $raw = strtoupper(trim($raw));
            }
            $data[$field] = $raw;
        }

        $data['la_social_science_autofilled'] = $request->boolean('la_social_science_autofilled');

        return $data;
    }

    private function validateStep4(Request $request): array
    {
        $prefixes = ['BUS ', 'MGT ', 'MKT ', 'FIN ', 'ACCT ', 'ECON ', 'ENT ', 'SRES '];

        $electiveRule = fn (string $label) => [
            'nullable',
            function ($attribute, $value, $fail) use ($label, $prefixes) {
                $v = strtoupper(trim((string) $value));
                if (! $v) {
                    return;
                }
                foreach ($prefixes as $p) {
                    if (str_starts_with($v, $p)) {
                        return;
                    }
                }
                $fail("{$label} must be a BUS, MGT, MKT, FIN, ACCT, ECON, ENT, or SRES course code.");
            },
        ];

        $request->validate([
            'core_elective_1' => $electiveRule('Business Elective 1'),
            'core_elective_2' => $electiveRule('Business Elective 2'),
        ]);

        $fields = [
            'core_ent118', 'core_mgt123', 'core_sres101', 'core_sres102',
            'core_acct205', 'core_acct206', 'core_fin226', 'core_math',
            'core_bus199', 'core_bus299a', 'core_mgt250', 'core_sres290',
            'core_stats', 'core_info_gateway', 'core_mkt345', 'core_ethics',
            'core_law', 'core_mgt365', 'core_bus399a', 'core_bus499a',
            'core_mgt475', 'core_bus498', 'core_elective_1', 'core_elective_2',
        ];

        $data = [];
        foreach ($fields as $field) {
            $raw = $request->input($field, '');
            if (in_array($field, ['core_elective_1', 'core_elective_2'])) {
                $raw = strtoupper(trim($raw));
            }
            $data[$field] = $raw;
        }

        $data['transfers'] = [];
        foreach ($request->input('transfers', []) as $row) {
            $institution = strip_tags(trim((string) ($row['institution'] ?? '')));
            $origName = strip_tags(trim((string) ($row['orig_name'] ?? '')));
            $cuaEquiv = strtoupper(strip_tags(trim((string) ($row['cua_equiv'] ?? ''))));
            $credits = is_numeric($row['credits'] ?? '') ? (float) $row['credits'] : null;
            $grade = strip_tags(trim((string) ($row['grade'] ?? '')));
            if ($institution && $origName) {
                $data['transfers'][] = compact('institution', 'origName', 'cuaEquiv', 'credits', 'grade');
            }
        }

        return $data;
    }

    private function validateStep5(Request $request): array
    {
        // Specialization courses — save dynamically keyed entries
        $data = [];
        foreach ($request->input('spec_courses', []) as $code => $status) {
            $safeCode = preg_replace('/[^A-Za-z0-9_ ]/', '', $code);
            $data["spec_course_{$safeCode}"] = in_array($status, ['completed', 'in_progress', 'not_yet']) ? $status : 'not_yet';
        }

        return $data;
    }

    private function validateStep6(Request $request): array
    {
        $validated = $request->validate([
            'credits_completed' => ['required', 'integer', 'min:0', 'max:200'],
            'gpa' => ['nullable', 'numeric', 'min:0', 'max:4.00'],
        ]);

        $validated['in_progress_courses'] = $request->input('in_progress_courses', []);
        $validated['projected_standing'] = $this->calculateStanding((int) $validated['credits_completed']);

        return $validated;
    }

    private function validateAccounting(Request $request): array
    {
        $data = ['acct_courses' => []];

        foreach ($request->input('acct_courses', []) as $code => $status) {
            $safeCode = preg_replace('/[^A-Za-z0-9 ]/', '', $code);
            if ($safeCode) {
                $data['acct_courses'][$safeCode] = in_array($status, ['completed', 'in_progress', 'not_yet'])
                    ? $status
                    : 'not_yet';
            }
        }

        $elective = trim($request->input('acct_elective', 'not_yet'));
        $data['acct_elective'] = in_array($elective, ['ACCT 480', 'ACCT 491', 'ECON 370', 'not_yet'])
            ? $elective
            : 'not_yet';

        return $data;
    }

    /** Returns [visualStep, visualTotal] for the wizard header. */
    private function computeWizardProgress(int $step, bool $isAccounting): array
    {
        if ($isAccounting) {
            // BS Accounting: 5 visual steps mapped from real steps 1, 3, 4, accounting(handled separately), 6
            return [match ($step) {
                1 => 1,
                3 => 2,
                4 => 3,
                6 => 5,
                default => 1,
            }, 5];
        }

        return [$step, 6];
    }

    private function computeSocialScienceAutoFill(array $data): ?array
    {
        $notCompleted = ['', 'not_yet', 'in_progress'];
        $sres101 = $data['core_sres101'] ?? '';
        $sres102 = $data['core_sres102'] ?? '';

        $sres101Done = ! in_array($sres101, $notCompleted);
        $sres102Done = ! in_array($sres102, $notCompleted);

        if (! $sres101Done && ! $sres102Done) {
            return null;
        }

        // Prefer SRES 101 when both are completed
        $fillValue = $sres101Done ? $sres101 : $sres102;
        $note = "Auto-filled: {$fillValue} fulfills the Social Science requirement through Economic Thought.";

        // Keep active unless student manually chose a different real course
        $current = $data['la_social_science'] ?? '';
        $manuallyChanged = $current && ! in_array($current, ['not_yet', '', $fillValue]);

        return [
            'value' => $fillValue,
            'note' => $note,
            'active' => ! $manuallyChanged,
        ];
    }

    private function determineCatalogYear(string $admitTerm): string
    {
        // Spring 2024 or after = post_2024, else pre_2024
        [$season, $year] = explode(' ', $admitTerm);
        $year = (int) $year;

        if ($year > 2024) {
            return 'post_2024';
        }

        if ($year === 2024 && $season === 'Spring') {
            return 'post_2024';
        }

        return 'pre_2024';
    }

    private function calculateStanding(int $credits): string
    {
        return match (true) {
            $credits >= 90 => 'Senior',
            $credits >= 60 => 'Junior',
            $credits >= 30 => 'Sophomore',
            default => 'Freshman',
        };
    }

    private function saveProfile(array $data): void
    {
        $user = Auth::user();

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'full_name' => $data['full_name'] ?? $user->name,
            'degree' => $data['degree'] ?? 'bsba',
            'catalog_year' => $data['catalog_year'] ?? 'post_2024',
            'admit_term' => $data['admit_term'] ?? '',
            'expected_graduation' => $data['expected_graduation'] ?? '',
            'specialization_1' => $data['specialization_1'] ?? null,
            'specialization_2' => $data['specialization_2'] ?: null,
            'specialization_3' => $data['specialization_3'] ?: null,
            'gpa' => isset($data['gpa']) && $data['gpa'] !== '' ? $data['gpa'] : null,
            'credits_completed' => (int) ($data['credits_completed'] ?? 0),
            'projected_standing' => $data['projected_standing'] ?? 'Freshman',
            'last_updated_at' => now(),
        ]);

        // Build student_courses rows from wizard data
        $courses = [];

        // Liberal arts
        $laMap = [
            'la_classical_philosophy' => ['category' => 'liberal_arts', 'name' => 'Classical Philosophy'],
            'la_modern_philosophy' => ['category' => 'liberal_arts', 'name' => 'Modern Philosophy'],
            'la_theology_1' => ['category' => 'liberal_arts', 'name' => 'Theology I'],
            'la_theology_2' => ['category' => 'liberal_arts', 'name' => 'Theology II'],
            'la_rhetoric' => ['category' => 'liberal_arts', 'name' => 'Rhetoric & Composition'],
            'la_natural_science' => ['category' => 'liberal_arts', 'name' => 'Natural Science'],
            'la_literature' => ['category' => 'liberal_arts', 'name' => 'Literature'],
            'la_fine_arts' => ['category' => 'liberal_arts', 'name' => 'Fine Arts'],
            'la_history_politics' => ['category' => 'liberal_arts', 'name' => 'History/Politics'],
            'la_language_1' => ['category' => 'liberal_arts', 'name' => 'Language I'],
            'la_language_2' => ['category' => 'liberal_arts', 'name' => 'Language II'],
            'la_phil_elective' => ['category' => 'liberal_arts', 'name' => 'Philosophy Elective'],
            'la_theology_elective' => ['category' => 'liberal_arts', 'name' => 'Theology Elective'],
            'la_social_science' => ['category' => 'liberal_arts', 'name' => 'Social Science'],
            'la_math_thinking' => ['category' => 'liberal_arts', 'name' => 'Math Thinking'],
        ];

        $ssAutofilled = ! empty($data['la_social_science_autofilled']);

        foreach ($laMap as $key => $meta) {
            $val = $data[$key] ?? '';
            if ($val && $val !== 'not_yet') {
                $note = null;
                if ($key === 'la_social_science' && $ssAutofilled) {
                    $note = 'Fulfilled through Economic Thought via business core requirement.';
                }
                $courses[] = [
                    'user_id' => $user->id,
                    'course_code' => $val,
                    'course_name' => $meta['name'],
                    'requirement_category' => $meta['category'],
                    'status' => 'completed',
                    'grade' => null,
                    'semester_completed' => null,
                    'notes' => $note,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Business core
        $coreMap = [
            'core_ent118' => ['code' => '',         'name' => 'ENT 118'],
            'core_mgt123' => ['code' => '',         'name' => 'MGT 123'],
            'core_sres101' => ['code' => '',         'name' => 'SRES 101/ECON'],
            'core_sres102' => ['code' => '',         'name' => 'SRES 102/ECON'],
            'core_acct205' => ['code' => 'ACCT 205', 'name' => 'Financial Accounting'],
            'core_acct206' => ['code' => 'ACCT 206', 'name' => 'Managerial Accounting'],
            'core_fin226' => ['code' => 'FIN 226',  'name' => 'Financial Management'],
            'core_math' => ['code' => '',         'name' => 'Math Requirement'],
            'core_bus199' => ['code' => 'BUS 199',  'name' => 'Career Discernment I'],
            'core_bus299a' => ['code' => 'BUS 299A', 'name' => 'Career Discernment II'],
            'core_mgt250' => ['code' => 'MGT 250',  'name' => 'Business Communications'],
            'core_sres290' => ['code' => 'SRES 290', 'name' => 'SRES 290'],
            'core_stats' => ['code' => '',         'name' => 'Statistics'],
            'core_info_gateway' => ['code' => '',         'name' => 'Info Management Gateway'],
            'core_mkt345' => ['code' => 'MKT 345',  'name' => 'Marketing Management'],
            'core_ethics' => ['code' => '',         'name' => 'Business Ethics'],
            'core_law' => ['code' => '',         'name' => 'Business Law'],
            'core_mgt365' => ['code' => 'MGT 365',  'name' => 'Quantitative Methods'],
            'core_bus399a' => ['code' => 'BUS 399A', 'name' => 'Career Discernment III'],
            'core_bus499a' => ['code' => 'BUS 499A', 'name' => 'Career Discernment IV'],
            'core_mgt475' => ['code' => 'MGT 475',  'name' => 'Business Strategy'],
            'core_bus498' => ['code' => 'BUS 498',  'name' => 'Comprehensive Assessment'],
            'core_elective_1' => ['code' => '',         'name' => 'Business Elective 1'],
            'core_elective_2' => ['code' => '',         'name' => 'Business Elective 2'],
        ];

        foreach ($coreMap as $key => $meta) {
            $val = $data[$key] ?? '';
            if (! $val || $val === 'not_yet') {
                continue;
            }

            if ($val === 'in_progress') {
                $code = $meta['code'] ?: null;
                if (! $code) {
                    continue;
                }
                $status = 'in_progress';
            } elseif ($val === 'Completed') {
                $code = $meta['code'];
                $status = 'completed';
            } else {
                $code = $val;
                $status = 'completed';
            }

            $courses[] = [
                'user_id' => $user->id,
                'course_code' => $code,
                'course_name' => $meta['name'],
                'requirement_category' => 'business_core',
                'status' => $status,
                'grade' => null,
                'semester_completed' => null,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // BS Accounting requirements from step_accounting
        if (($data['degree'] ?? '') === 'bs_accounting') {
            $acctNames = [
                'ACCT 310' => 'Intermediate Accounting I',
                'ACCT 311' => 'Intermediate Accounting II',
                'ACCT 312' => 'Intermediate Accounting III',
                'ACCT 315' => 'Cost Accounting',
                'ACCT 412' => 'Auditing',
                'ACCT 417' => 'Government and Non-Profit Accounting',
                'ACCT 418' => 'Advanced Accounting',
                'ACCT 419' => 'Federal Income Taxation',
                'ACCT 422' => 'Accounting Analytics',
                'ACCT 442' => 'Accounting Ethics',
                'FIN 332' => 'Corporate Finance',
                'FIN 334' => 'Investments',
                'ACCT 480' => 'Accounting Elective',
                'ACCT 491' => 'Accounting Elective',
                'ECON 370' => 'Accounting Elective',
            ];

            foreach ($data['acct_courses'] ?? [] as $code => $status) {
                if ($status !== 'not_yet' && isset($acctNames[$code])) {
                    $courses[] = [
                        'user_id' => $user->id,
                        'course_code' => $code,
                        'course_name' => $acctNames[$code],
                        'requirement_category' => 'accounting',
                        'status' => $status,
                        'grade' => null,
                        'semester_completed' => null,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            $elective = $data['acct_elective'] ?? 'not_yet';
            if ($elective && $elective !== 'not_yet' && isset($acctNames[$elective])) {
                $courses[] = [
                    'user_id' => $user->id,
                    'course_code' => $elective,
                    'course_name' => 'Accounting Elective',
                    'requirement_category' => 'accounting',
                    'status' => 'completed',
                    'grade' => null,
                    'semester_completed' => null,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Specialization courses from step 5
        foreach ($data as $key => $val) {
            if (str_starts_with($key, 'spec_course_') && $val !== 'not_yet') {
                $courseCode = str_replace('spec_course_', '', $key);
                $courseCode = str_replace('_', ' ', $courseCode);
                $courses[] = [
                    'user_id' => $user->id,
                    'course_code' => $courseCode,
                    'course_name' => $courseCode,
                    'requirement_category' => 'specialization',
                    'status' => $val,
                    'grade' => null,
                    'semester_completed' => null,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // In-progress courses from step 6
        foreach ($data['in_progress_courses'] ?? [] as $courseCode) {
            $courseCode = strip_tags((string) $courseCode);
            if ($courseCode) {
                $courses[] = [
                    'user_id' => $user->id,
                    'course_code' => $courseCode,
                    'course_name' => $courseCode,
                    'requirement_category' => 'in_progress',
                    'status' => 'in_progress',
                    'grade' => null,
                    'semester_completed' => null,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Transfer credits from step 4
        foreach ($data['transfers'] ?? [] as $transfer) {
            $courses[] = [
                'user_id' => $user->id,
                'course_code' => $transfer['cuaEquiv'] ?: $transfer['origName'],
                'course_name' => $transfer['origName'],
                'requirement_category' => 'transfer_credit',
                'status' => 'completed',
                'grade' => $transfer['grade'] ?: null,
                'semester_completed' => null,
                'notes' => 'Transfer from '.$transfer['institution'].($transfer['credits'] !== null ? ' ('.$transfer['credits'].' credits)' : ''),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($courses) {
            StudentCourse::insert($courses);
        }
    }
}
