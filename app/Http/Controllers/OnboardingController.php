<?php

namespace App\Http\Controllers;

use App\Models\StudentCourse;
use App\Models\StudentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $specializationsJson = json_decode(file_get_contents(storage_path('app/specializations.json')), true);

        return view("onboarding.step{$step}", [
            'step' => $step,
            'totalSteps' => self::TOTAL_STEPS,
            'data' => $data,
            'specializations' => $specializationsJson,
            'socialScienceAutoFill' => $step === 3 ? $this->computeSocialScienceAutoFill($data) : null,
        ]);
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
