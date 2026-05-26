<?php

namespace App\Http\Controllers;

use App\Models\StudentCourse;
use App\Models\StudentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    private const TOTAL_STEPS = 6;

    private const ADMIT_TERMS = [
        'Fall 2020', 'Spring 2021', 'Fall 2021', 'Spring 2022', 'Fall 2022',
        'Spring 2023', 'Fall 2023', 'Spring 2024', 'Fall 2024', 'Spring 2025',
        'Fall 2025', 'Spring 2026',
    ];

    private const GRADUATION_TERMS = [
        'Spring 2025', 'Fall 2025', 'Spring 2026', 'Fall 2026', 'Spring 2027',
        'Fall 2027', 'Spring 2028', 'Fall 2028', 'Spring 2029', 'Fall 2029', 'Spring 2030',
    ];

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

        $requirements = json_decode((string) file_get_contents(storage_path('app/requirements.json')), true) ?? [];
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

        $requirements = json_decode((string) file_get_contents(storage_path('app/requirements.json')), true) ?? [];

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
            'admit_term' => ['required', 'string', Rule::in(self::ADMIT_TERMS)],
            'degree' => ['required', 'in:bsba,bs_accounting'],
            'expected_graduation' => ['required', 'string', Rule::in(self::GRADUATION_TERMS)],
        ]);

        // Auto-determine catalog year from admit term
        $catalogYear = $this->determineCatalogYear($validated['admit_term']);
        $validated['catalog_year'] = $catalogYear;

        return $validated;
    }

    private function validateStep2(Request $request, array $existingData): array
    {
        $requirements = json_decode((string) file_get_contents(storage_path('app/requirements.json')), true) ?? [];
        $catalogYear = $existingData['catalog_year'] ?? 'post_2024';
        $validSpecs = array_keys($requirements[$catalogYear]['specializations'] ?? []);

        $validated = $request->validate([
            'specialization_1' => ['required', 'string', Rule::in($validSpecs)],
            'specialization_2' => ['nullable', 'string', Rule::in(array_merge(['', 'null'], $validSpecs))],
            'specialization_3' => ['nullable', 'string', Rule::in(array_merge(['', 'null'], $validSpecs))],
        ]);

        return $validated;
    }

    private function validateStep3(Request $request): array
    {
        $allowedSelects = $this->allowedLaSelects();

        $rules = [
            'la_phil_elective' => [
                'nullable',
                'string',
                'max:30',
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
                'string',
                'max:30',
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
        ];

        foreach ($allowedSelects as $field => $allowed) {
            $rules[$field] = ['nullable', Rule::in($allowed)];
        }

        $request->validate($rules);

        $data = [];
        foreach (array_keys($allowedSelects) as $field) {
            $data[$field] = $request->input($field, '');
        }

        foreach (['la_phil_elective', 'la_theology_elective'] as $field) {
            $data[$field] = strtoupper(trim($request->input($field, '')));
        }

        $data['la_social_science_autofilled'] = $request->boolean('la_social_science_autofilled');

        return $data;
    }

    private function validateStep4(Request $request): array
    {
        $requirements = json_decode((string) file_get_contents(storage_path('app/requirements.json')), true) ?? [];
        $allowedCore = $this->allowedCoreSelects($requirements);

        $prefixes = ['BUS ', 'MGT ', 'MKT ', 'FIN ', 'ACCT ', 'ECON ', 'ENT ', 'SRES '];

        $electiveRule = fn (string $label) => [
            'nullable',
            'string',
            'max:20',
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

        $coreRules = [];
        foreach ($allowedCore as $field => $allowed) {
            $coreRules[$field] = ['nullable', Rule::in($allowed)];
        }

        $request->validate(array_merge($coreRules, [
            'core_elective_1' => $electiveRule('Business Elective 1'),
            'core_elective_2' => $electiveRule('Business Elective 2'),
        ]));

        $data = [];
        foreach (array_keys($allowedCore) as $field) {
            $data[$field] = $request->input($field, '');
        }

        foreach (['core_elective_1', 'core_elective_2'] as $field) {
            $data[$field] = strtoupper(trim($request->input($field, '')));
        }

        // Transfer credits — cap at 15 rows, enforce field length limits
        $data['transfers'] = [];
        foreach (array_slice($request->input('transfers', []), 0, 15) as $row) {
            $institution = mb_substr(strip_tags(trim((string) ($row['institution'] ?? ''))), 0, 255);
            $origName = mb_substr(strip_tags(trim((string) ($row['orig_name'] ?? ''))), 0, 255);
            $cuaEquiv = mb_substr(strtoupper(strip_tags(trim((string) ($row['cua_equiv'] ?? '')))), 0, 20);
            $creditsRaw = $row['credits'] ?? '';
            if (is_numeric($creditsRaw)) {
                $credits = (float) $creditsRaw;
                if ($credits < 0 || $credits > 50) {
                    $credits = null;
                }
            } else {
                $credits = null;
            }
            $grade = mb_substr(strip_tags(trim((string) ($row['grade'] ?? ''))), 0, 5);
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
        foreach (array_slice($request->input('spec_courses', []), 0, 50) as $code => $status) {
            $safeCode = mb_substr(preg_replace('/[^A-Za-z0-9_ ]/', '', (string) $code), 0, 20);
            if (! $safeCode) {
                continue;
            }
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

        // Cap at 20 courses, truncate each code to 20 chars, strip tags
        $validated['in_progress_courses'] = array_values(array_filter(
            array_map(
                fn ($c) => mb_substr(strtoupper(strip_tags(trim((string) $c))), 0, 20),
                array_slice($request->input('in_progress_courses', []), 0, 20)
            ),
            fn ($c) => $c !== ''
        ));

        $validated['projected_standing'] = $this->calculateStanding((int) $validated['credits_completed']);

        return $validated;
    }

    private function validateAccounting(Request $request): array
    {
        $data = ['acct_courses' => []];

        foreach (array_slice($request->input('acct_courses', []), 0, 20) as $code => $status) {
            $safeCode = mb_substr(preg_replace('/[^A-Za-z0-9 ]/', '', (string) $code), 0, 20);
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

    /** Allowed values for every LA select dropdown, mirroring the view's <option> lists. */
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

    /** Allowed values for every Business Core select, including options from requirements.json. */
    private function allowedCoreSelects(array $requirements): array
    {
        $infoGateway = array_unique(array_merge(
            $requirements['post_2024']['business_core_options']['info_gateway'] ?? [],
            $requirements['pre_2024']['business_core_options']['info_gateway'] ?? [],
        ));

        $ethics = array_unique(array_merge(
            $requirements['post_2024']['business_core_options']['ethics'] ?? [],
            $requirements['pre_2024']['business_core_options']['ethics'] ?? [],
        ));

        $lawCodes = [];
        foreach (['post_2024', 'pre_2024'] as $cy) {
            foreach ($requirements[$cy]['business_core_options']['law'] ?? [] as $opt) {
                $lawCodes[] = is_array($opt) ? $opt['code'] : $opt;
            }
        }
        $lawCodes = array_unique($lawCodes);

        $status3 = ['not_yet', 'in_progress', 'Completed'];

        return [
            'core_ent118' => ['not_yet', 'ENT 118A', 'ENT 118B'],
            'core_mgt123' => ['not_yet', 'MGT 123A', 'MGT 123B'],
            'core_sres101' => ['not_yet', 'SRES 101', 'ECON 100', 'ECON 102'],
            'core_sres102' => ['not_yet', 'SRES 102', 'ECON 200', 'ECON 101'],
            'core_sres290' => $status3,
            'core_acct205' => $status3,
            'core_acct206' => $status3,
            'core_fin226' => $status3,
            'core_mgt250' => $status3,
            'core_mgt365' => array_merge($status3, ['BUS 603']),
            'core_mgt475' => $status3,
            'core_bus498' => $status3,
            'core_bus199' => $status3,
            'core_bus299a' => array_merge($status3, ['BUS 299A', 'MKT 299']),
            'core_bus399a' => array_merge($status3, ['BUS 399A', 'MKT 399']),
            'core_bus499a' => array_merge($status3, ['BUS 499A', 'MKT 499']),
            'core_mkt345' => ['not_yet', 'in_progress', 'MKT 345'],
            'core_stats' => ['not_yet', 'MGT 265', 'ECON 223'],
            'core_math' => ['not_yet', 'MATH 110', 'MATH 111', 'Level 1 or 2 Exempt'],
            'core_info_gateway' => array_merge(['not_yet'], $infoGateway),
            'core_ethics' => array_merge(['not_yet'], $ethics),
            'core_law' => array_merge(['not_yet'], $lawCodes),
        ];
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
