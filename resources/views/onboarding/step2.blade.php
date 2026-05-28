<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Profile Setup — Step 2 | Busch School</title>

    <link rel="stylesheet" href="/fonts/fonts.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --cua-red:   #B41100;
            --cua-navy:  #0a3255;
            --cua-gold:  #C9A84C;
            --cua-dark:  #0a3255;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background: #f0f0f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            color: #1a1a1a;
        }

        .wizard-header { text-align: center; margin-bottom: 0.5rem; width: 100%; max-width: 680px; }

        .wizard-logo {
            display: block;
            margin: 0 auto 1rem;
            height: 60px;
            object-fit: contain;
        }

        .wizard-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--cua-navy);
            text-align: center;
            margin: 0 0 0.25rem;
        }

        .wizard-subtitle {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin: 0 0 1rem;
        }

        .progress-bar-container {
            width: 100%;
            max-width: 680px;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            margin-bottom: 0.75rem;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: var(--cua-red);
            border-radius: 4px;
            transition: width 0.4s ease;
        }

        .step-labels {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 680px;
            margin-bottom: 1.5rem;
        }

        .step-label {
            font-size: 0.7rem;
            color: #aaa;
            font-weight: 500;
            text-align: center;
            flex: 1;
        }

        .step-label.active { color: var(--cua-red); font-weight: 700; }
        .step-label.done   { color: var(--cua-navy); }

        .wizard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 2.5rem;
            width: 100%;
            max-width: 680px;
            margin-bottom: 2rem;
        }

        .step-heading {
            font-family: 'Oswald', sans-serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--cua-navy);
            margin: 0 0 0.25rem;
            padding-bottom: 0.4rem;
            border-bottom: 2px solid var(--cua-gold);
        }

        .step-subtext {
            font-size: 0.88rem;
            color: #666;
            margin: 0 0 1.25rem;
        }

        .spec-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.6rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 540px) {
            .spec-grid { grid-template-columns: 1fr; }
        }

        .spec-card {
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.85rem 1rem;
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .spec-card:hover  { border-color: var(--cua-navy); background: #f8faff; }
        .spec-card.selected { border-color: var(--cua-red); background: #fff5f5; }
        .spec-card.disabled { opacity: 0.45; cursor: not-allowed; }

        .spec-card input[type="checkbox"] {
            accent-color: var(--cua-red);
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
            margin-top: 0.15rem;
        }

        .spec-card-text strong {
            display: block;
            font-size: 0.92rem;
            color: #1a1a1a;
            margin-bottom: 0.2rem;
        }

        .spec-card-text p {
            font-size: 0.8rem;
            color: #666;
            margin: 0;
            line-height: 1.4;
        }

        .btn-primary {
            background: var(--cua-red);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            letter-spacing: 0.04em;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary:hover { background: #8c0e00; }

        .btn-secondary {
            background: transparent;
            color: var(--cua-navy);
            border: 1.5px solid var(--cua-navy);
            padding: 0.7rem 1.5rem;
            font-size: 0.95rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover { background: #f0f4ff; }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid #eee;
        }

        .alert-warning {
            background: #fffbea;
            border-left: 4px solid var(--cua-gold);
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin: 0.75rem 0;
            font-size: 0.9rem;
        }

        .alert-info {
            background: #e8f0fe;
            border-left: 4px solid var(--cua-navy);
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin: 0.75rem 0;
            font-size: 0.9rem;
            color: var(--cua-dark);
        }

        .counter-badge {
            display: inline-block;
            background: var(--cua-navy);
            color: white;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.15rem 0.55rem;
            border-radius: 12px;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>

    @include('onboarding.partials.header', ['step' => $step, 'totalSteps' => $totalSteps])

    @php
        $catalogYear = $data['catalog_year'] ?? 'post_2024';
        $isDoubleMajor = $degree === 'double_major';
        $isMinor = $degree === 'business_minor';
        $specs = $requirements[$catalogYear]['specializations'] ?? [];
        $pairs = $requirements['double_major']['pairs'] ?? [];

        $descriptions = [
            'people_and_organizations'          => 'Focus on human resources, organizational behavior, and leadership.',
            'technology_and_operations_management' => 'Explore supply chain, operations, and technology-driven processes.',
            'data_analytics_for_business'       => 'Apply data analysis and business intelligence tools to real decisions.',
            'sports_management'                 => 'Manage sports organizations, events, and athletic programs. Requires double specialization.',
            'finance'                           => 'Study financial markets, investments, and corporate finance. Requires MATH 111.',
            'entrepreneurship'                  => 'Launch and grow ventures through innovation. Requires double specialization.',
            'marketing'                         => 'Develop marketing strategy, brand management, and consumer insights.',
            'sales'                             => 'Build expertise in sales techniques, CRM, and revenue generation.',
            'markets_and_political_economy'     => 'Examine market institutions, regulation, and political economy.',
            'accounting'                        => 'Master financial and managerial accounting. Requires double specialization + MATH 111.',
            'international_business'            => 'Navigate global markets. Requires language minor and abroad experience.',
            'management'                        => 'Lead organizations through strategy, operations, and organizational design.',
            'hr_management'                     => 'Develop expertise in human resources, talent, and employee relations.',
            'operations_management'             => 'Optimize business processes, logistics, and supply chains.',
            'technology_management'             => 'Integrate technology and management for competitive advantage.',
            'family_business'                   => 'Navigate the unique dynamics of family-owned and closely-held businesses.',
            'mathematical_finance'              => 'Combine quantitative methods and finance theory. Requires MATH 111.',
        ];

        $mustDoubleSpecs = array_values(array_keys(array_filter($specs, fn ($s) => ! empty($s['must_double_specialize']))));
        $mathReqSpecs    = array_values(array_keys(array_filter($specs, fn ($s) => ! empty($s['prerequisites']) && in_array('MATH 111', (array) $s['prerequisites']))));

        $preSelected = array_filter([
            $data['specialization_1'] ?? null,
            $data['specialization_2'] ?? null,
            $data['specialization_3'] ?? null,
        ]);
        $preSelectedJson = json_encode(array_values($preSelected));

        $selectedPair = $data['double_major_pair'] ?? '';
    @endphp

    @if ($isDoubleMajor)
        {{-- ── Double Major: Focus Area Pair Selection ──────────────── --}}
        <div class="wizard-card"
             x-data="{
                selectedPair: '{{ old('double_major_pair', $selectedPair) }}',
                pairs: {{ Js::from($pairs) }},
                courseStatuses: {
                    @foreach($pairs as $pKey => $pair)
                        @foreach($pair['courses'] as $course)
                            @php $safeKey = str_replace(' ', '_', $course); @endphp
                            '{{ $safeKey }}': '{{ old('spec_courses.'.$safeKey, $data['spec_course_'.$safeKey] ?? 'not_yet') }}',
                        @endforeach
                    @endforeach
                },
                get activePairCourses() {
                    if (!this.selectedPair || !this.pairs[this.selectedPair]) return [];
                    return this.pairs[this.selectedPair].courses;
                }
             }"
        >
            @if ($errors->any())
                <div class="alert-warning">{{ $errors->first() }}</div>
            @endif

            <h3 class="step-heading">Choose Your Focus Area Pair</h3>
            <p class="step-subtext">
                Select the pair of courses that forms your focus area for the BA in Business.
                You must take both courses in your chosen pair.
            </p>

            <form id="pair-form" method="POST" action="{{ route('onboarding.save', 2) }}">
                @csrf
                <x-honeypot />

                <div class="spec-grid">
                    @foreach($pairs as $pKey => $pair)
                        <div
                            class="spec-card"
                            :class="{ 'selected': selectedPair === '{{ $pKey }}' }"
                            @click="selectedPair = '{{ $pKey }}'"
                        >
                            <input
                                type="radio"
                                name="double_major_pair"
                                value="{{ $pKey }}"
                                x-model="selectedPair"
                                @click.stop
                                style="accent-color: var(--cua-red); width:1rem; height:1rem; flex-shrink:0; margin-top:0.15rem;"
                            >
                            <div class="spec-card-text">
                                <strong>{{ $pair['label'] }}</strong>
                                <p>{{ implode(' + ', $pair['courses']) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Course status for selected pair --}}
                <div x-show="selectedPair" x-transition style="margin-top:1.25rem;">
                    <h3 class="step-heading" style="margin-top:0;">Course Status</h3>
                    <p class="step-subtext">Mark your progress on each course in your pair.</p>
                    <div class="form-grid">
                        @foreach($pairs as $pKey => $pair)
                            @foreach($pair['courses'] as $course)
                                @php $safeKey = str_replace(' ', '_', $course); @endphp
                                <div class="form-group" x-show="selectedPair === '{{ $pKey }}'">
                                    <label class="field-label" style="font-size:0.88rem;">{{ $course }}</label>
                                    <select name="spec_courses[{{ $safeKey }}]" x-model="courseStatuses['{{ $safeKey }}']"
                                            style="width:100%;padding:0.55rem 0.8rem;border:1.5px solid #ccc;border-radius:6px;font-size:0.93rem;font-family:'Roboto',sans-serif;background:#fff;">
                                        <option value="not_yet">Not yet taken</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('onboarding.step', 1) }}" class="btn-secondary">← Back</a>
                    <button type="submit" class="btn-primary"
                            onclick="if(!document.querySelector('input[name=double_major_pair]:checked')){alert('Please select a focus area pair.');return false;}">
                        Next: Business Core →
                    </button>
                </div>

            </form>
        </div>

    @elseif ($isMinor)
        {{-- ── Business Minor: Minor Selection ─────────────────────── --}}
        @php
            $minors = $requirements['business_minors'] ?? [];
            $selectedMinorKey = old('business_minor', $data['business_minor'] ?? '');
        @endphp
        <div class="wizard-card">
            @if ($errors->any())
                <div class="alert-warning">{{ $errors->first() }}</div>
            @endif

            <h3 class="step-heading">Choose Your Business Minor</h3>
            <p class="step-subtext">
                Select the minor you are pursuing. This will set up the correct course requirements for your profile.
            </p>

            <form method="POST" action="{{ route('onboarding.save', 2) }}">
                @csrf
                <x-honeypot />

                <div class="spec-grid">
                    @foreach($minors as $mKey => $minor)
                        <label
                            class="spec-card"
                            style="cursor:pointer;"
                            :class="''"
                        >
                            <input
                                type="radio"
                                name="business_minor"
                                value="{{ $mKey }}"
                                {{ $selectedMinorKey === $mKey ? 'checked' : '' }}
                                style="accent-color: var(--cua-red); width:1rem; height:1rem; flex-shrink:0; margin-top:0.15rem;"
                                onchange="this.closest('.spec-grid').querySelectorAll('.spec-card').forEach(c=>c.classList.remove('selected')); this.closest('.spec-card').classList.add('selected');"
                            >
                            <div class="spec-card-text">
                                <strong>{{ $minor['label'] }}</strong>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="form-actions">
                    <a href="{{ route('onboarding.step', 1) }}" class="btn-secondary">← Back</a>
                    <button type="submit" class="btn-primary"
                            onclick="if(!document.querySelector('input[name=business_minor]:checked')){alert('Please select a minor.');return false;}">
                        Next: Minor Courses →
                    </button>
                </div>

            </form>
        </div>

        <script>
            // Pre-select the saved minor card on page load
            document.addEventListener('DOMContentLoaded', function () {
                const checked = document.querySelector('input[name=business_minor]:checked');
                if (checked) checked.closest('.spec-card').classList.add('selected');
            });
        </script>

    @else
        {{-- ── BSBA: Specialization Selection ───────────────────────── --}}
        <div class="wizard-card"
             x-data="{
                selected: {{ $preSelectedJson }},
                maxAllowed: 3,
                needDouble: {{ Js::from($mustDoubleSpecs) }},
                mathReq: {{ Js::from($mathReqSpecs) }},
                toggle(key) {
                    const idx = this.selected.indexOf(key);
                    if (idx >= 0) {
                        this.selected.splice(idx, 1);
                    } else if (this.selected.length < this.maxAllowed) {
                        this.selected.push(key);
                    }
                },
                isSelected(key) { return this.selected.includes(key); },
                isDisabled(key) { return this.selected.length >= this.maxAllowed && !this.selected.includes(key); },
                hasDouble() {
                    return this.selected.some(s => this.needDouble.includes(s));
                },
                hasMath111() {
                    return this.selected.some(s => this.mathReq.includes(s));
                },
                submitForm() {
                    if (this.selected.length === 0) {
                        alert('Please select at least one specialization.');
                        return;
                    }
                    this.$refs.spec1.value = this.selected[0] || '';
                    this.$refs.spec2.value = this.selected[1] || '';
                    this.$refs.spec3.value = this.selected[2] || '';
                    document.getElementById('spec-form').submit();
                }
             }"
        >

            @if ($errors->any())
                <div class="alert-warning">{{ $errors->first() }}</div>
            @endif

            <h3 class="step-heading">
                Choose Your Specialization(s)
                <span class="counter-badge" x-text="selected.length + ' / 3'"></span>
            </h3>
            <p class="step-subtext">
                Select 1–3 specializations. Some require a second specialization (noted below).
                You are on the <strong>{{ $catalogYear === 'post_2024' ? 'Post-2024' : 'Pre-2024' }}</strong> catalog.
            </p>

            <div x-show="hasDouble() && selected.length < 2" x-transition class="alert-warning">
                Sports Management / Entrepreneurship require a second specialization.
            </div>

            <div x-show="hasMath111()" x-transition class="alert-info">
                Note: MATH 111 is required for one or more of your selected specializations.
            </div>

            <form id="spec-form" method="POST" action="{{ route('onboarding.save', 2) }}">
                @csrf
                <x-honeypot />

                <input type="hidden" name="specialization_1" x-ref="spec1">
                <input type="hidden" name="specialization_2" x-ref="spec2">
                <input type="hidden" name="specialization_3" x-ref="spec3">

                <div class="spec-grid">
                    @foreach($specs as $key => $spec)
                        <div
                            class="spec-card"
                            :class="{
                                'selected': isSelected('{{ $key }}'),
                                'disabled': isDisabled('{{ $key }}')
                            }"
                            @click="!isDisabled('{{ $key }}') && toggle('{{ $key }}')"
                        >
                            <input
                                type="checkbox"
                                :checked="isSelected('{{ $key }}')"
                                :disabled="isDisabled('{{ $key }}')"
                                @click.stop="toggle('{{ $key }}')"
                                tabindex="-1"
                            >
                            <div class="spec-card-text">
                                <strong>{{ $spec['name'] }}</strong>
                                <p>{{ $descriptions[$key] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-actions">
                    <a href="{{ route('onboarding.step', 1) }}" class="btn-secondary">← Back</a>
                    <button type="button" class="btn-primary" @click="submitForm()">Next: Liberal Arts →</button>
                </div>

            </form>
        </div>
    @endif

</body>
</html>
