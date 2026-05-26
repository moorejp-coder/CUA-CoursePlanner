<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Profile Setup — Step 4 | Busch School</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --cua-red:   #B41100;
            --cua-navy:  #003366;
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
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--cua-navy);
            margin: 1.5rem 0 0.4rem;
            padding-bottom: 0.3rem;
            border-bottom: 2px solid var(--cua-gold);
        }

        .step-heading:first-of-type { margin-top: 0; }

        .section-note {
            font-size: 0.82rem;
            color: #555;
            margin: 0 0 0.75rem;
            line-height: 1.5;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 0.25rem;
        }

        @media (max-width: 520px) {
            .form-grid { grid-template-columns: 1fr; }
        }

        .form-group { margin-bottom: 0.75rem; }
        .form-group.narrow { max-width: 320px; }

        label.field-label {
            display: block;
            font-weight: 500;
            font-size: 0.88rem;
            margin-bottom: 0.35rem;
            color: #333;
        }

        select, .text-input {
            width: 100%;
            padding: 0.55rem 0.8rem;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            font-size: 0.93rem;
            font-family: 'Roboto', sans-serif;
            transition: border-color 0.2s;
            background: #fff;
        }

        select:focus, .text-input:focus {
            outline: none;
            border-color: var(--cua-navy);
            box-shadow: 0 0 0 2px rgba(0,51,102,0.12);
        }

        .text-input.input-error { border-color: var(--cua-red); }
        .text-input.input-valid { border-color: #22c55e; }

        .field-hint {
            font-size: 0.78rem;
            color: #777;
            margin: 0.3rem 0 0;
            line-height: 1.4;
        }

        .field-error {
            font-size: 0.8rem;
            color: var(--cua-red);
            margin: 0.25rem 0 0;
            min-height: 1.1em;
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
            margin: 0 0 1rem;
            font-size: 0.9rem;
        }

        .alert-info {
            background: #e8f0fe;
            border-left: 4px solid var(--cua-navy);
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin: 0.5rem 0 0.75rem;
            font-size: 0.88rem;
            color: var(--cua-dark);
        }

        .catalog-badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.1rem 0.5rem;
            border-radius: 4px;
            margin-left: 0.4rem;
            vertical-align: middle;
        }

        .badge-pre  { background: #fef3c7; color: #92400e; }
        .badge-post { background: #d1fae5; color: #065f46; }

        .transfer-table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        .transfer-table th {
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.05em; color: #666; padding: 0.4rem 0.5rem;
            border-bottom: 1.5px solid #e0e0e0; text-align: left; background: #fafafa;
        }
        .transfer-table td { padding: 0.4rem 0.5rem; vertical-align: top; }
        .transfer-table input, .transfer-table select {
            width: 100%; padding: 0.4rem 0.5rem; border: 1.5px solid #ccc; border-radius: 5px;
            font-size: 0.85rem; font-family: 'Roboto', sans-serif; background: #fff;
        }
        .transfer-table input:focus, .transfer-table select:focus {
            outline: none; border-color: var(--cua-navy);
        }
        .btn-remove {
            background: transparent; border: none; color: #999; cursor: pointer;
            font-size: 1.1rem; padding: 0.3rem 0.4rem; line-height: 1;
        }
        .btn-remove:hover { color: var(--cua-red); }
        .btn-add-row {
            background: transparent; border: 1.5px solid var(--cua-navy); color: var(--cua-navy);
            padding: 0.45rem 1rem; border-radius: 5px; font-size: 0.85rem;
            font-family: 'Roboto', sans-serif; cursor: pointer; margin-top: 0.6rem;
        }
        .btn-add-row:hover { background: #f0f4ff; }
    </style>
</head>
<body>

    @include('onboarding.partials.header', ['step' => $step, 'totalSteps' => $totalSteps])

    @php
        $catalogYear  = $data['catalog_year'] ?? 'post_2024';
        $isPost2024   = $catalogYear === 'post_2024';
        $isSales      = in_array('sales', [
            $data['specialization_1'] ?? '',
            $data['specialization_2'] ?? '',
            $data['specialization_3'] ?? '',
        ]);
        $isSingleSpec = empty($data['specialization_2'] ?? '') && empty($data['specialization_3'] ?? '');
        $showElectives = ! $isPost2024 && $isSingleSpec;
        $v = fn (string $key, string $default = 'not_yet') => old($key, $data[$key] ?? $default);
        $coreOptions = $requirements[$catalogYear]['business_core_options'] ?? [];
    @endphp

    <div class="wizard-card">

        @if ($errors->any())
            <div class="alert-warning">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('onboarding.save', 4) }}">
            @csrf
            <x-honeypot />

            {{-- ─── SECTION 1: Business Foundations ─────────────────────────── --}}
            <h3 class="step-heading">1. Business Foundations</h3>
            <div class="form-grid">

                <div class="form-group">
                    <label class="field-label" for="core_ent118">ENT 118 — The Vocation of Business</label>
                    <select id="core_ent118" name="core_ent118">
                        <option value="not_yet"  {{ $v('core_ent118') === 'not_yet'  ? 'selected' : '' }}>Not yet completed</option>
                        <option value="ENT 118A" {{ $v('core_ent118') === 'ENT 118A' ? 'selected' : '' }}>ENT 118A (Fall)</option>
                        <option value="ENT 118B" {{ $v('core_ent118') === 'ENT 118B' ? 'selected' : '' }}>ENT 118B (Spring)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_mgt123">MGT 123 — Foundations of Business</label>
                    <select id="core_mgt123" name="core_mgt123">
                        <option value="not_yet"  {{ $v('core_mgt123') === 'not_yet'  ? 'selected' : '' }}>Not yet completed</option>
                        <option value="MGT 123A" {{ $v('core_mgt123') === 'MGT 123A' ? 'selected' : '' }}>MGT 123A (Spring, freshmen)</option>
                        <option value="MGT 123B" {{ $v('core_mgt123') === 'MGT 123B' ? 'selected' : '' }}>MGT 123B (Fall/Spring, soph+)</option>
                    </select>
                </div>

            </div>

            {{-- ─── SECTION 2: Economic Thought ──────────────────────────────── --}}
            <h3 class="step-heading">2. Economic Thought</h3>
            <div class="form-grid">

                <div class="form-group">
                    <label class="field-label" for="core_sres101">SRES 101 — Markets and Prosperity I</label>
                    <select id="core_sres101" name="core_sres101">
                        <option value="not_yet"        {{ $v('core_sres101') === 'not_yet'        ? 'selected' : '' }}>Not yet completed</option>
                        <option value="SRES 101"       {{ $v('core_sres101') === 'SRES 101'       ? 'selected' : '' }}>SRES 101</option>
                        <option value="ECON 100"       {{ $v('core_sres101') === 'ECON 100'       ? 'selected' : '' }}>ECON 100 (equivalent)</option>
                        <option value="ECON 102"       {{ $v('core_sres101') === 'ECON 102'       ? 'selected' : '' }}>ECON 102 (equivalent)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_sres102">SRES 102 — Markets and Prosperity II</label>
                    <select id="core_sres102" name="core_sres102">
                        <option value="not_yet"        {{ $v('core_sres102') === 'not_yet'        ? 'selected' : '' }}>Not yet completed</option>
                        <option value="SRES 102"       {{ $v('core_sres102') === 'SRES 102'       ? 'selected' : '' }}>SRES 102</option>
                        <option value="ECON 200"       {{ $v('core_sres102') === 'ECON 200'       ? 'selected' : '' }}>ECON 200 (equivalent)</option>
                        <option value="ECON 101"       {{ $v('core_sres102') === 'ECON 101'       ? 'selected' : '' }}>ECON 101 (equivalent)</option>
                    </select>
                </div>

                @if ($isPost2024)
                    <div class="form-group">
                        <label class="field-label" for="core_sres290">
                            SRES 290 — Catholic Social Thought in Business
                            <span class="catalog-badge badge-post">Post-2024</span>
                        </label>
                        <select id="core_sres290" name="core_sres290">
                            <option value="not_yet"     {{ $v('core_sres290') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                            <option value="in_progress" {{ $v('core_sres290') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Completed"   {{ $v('core_sres290') === 'Completed'   ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                @endif

            </div>

            {{-- ─── SECTION 3: Accounting ─────────────────────────────────────── --}}
            <h3 class="step-heading">3. Accounting</h3>
            <p class="section-note">ACCT 205 is a prerequisite for ACCT 206 and FIN 226.</p>
            <div class="form-grid">

                <div class="form-group">
                    <label class="field-label" for="core_acct205">ACCT 205 — Introductory Accounting</label>
                    <select id="core_acct205" name="core_acct205">
                        <option value="not_yet"     {{ $v('core_acct205') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                        <option value="in_progress" {{ $v('core_acct205') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed"   {{ $v('core_acct205') === 'Completed'   ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_acct206">ACCT 206 — Managerial Accounting</label>
                    <select id="core_acct206" name="core_acct206">
                        <option value="not_yet"     {{ $v('core_acct206') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                        <option value="in_progress" {{ $v('core_acct206') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed"   {{ $v('core_acct206') === 'Completed'   ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

            </div>

            {{-- ─── SECTION 4: Finance ────────────────────────────────────────── --}}
            <h3 class="step-heading">4. Finance</h3>
            <div class="form-group narrow">
                <label class="field-label" for="core_fin226">FIN 226 — Introduction to Finance</label>
                <select id="core_fin226" name="core_fin226">
                    <option value="not_yet"     {{ $v('core_fin226') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                    <option value="in_progress" {{ $v('core_fin226') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Completed"   {{ $v('core_fin226') === 'Completed'   ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            {{-- ─── SECTION 5: Communications ─────────────────────────────────── --}}
            <h3 class="step-heading">5. Communications</h3>
            <div class="form-group narrow">
                <label class="field-label" for="core_mgt250">MGT 250 — Business Communications</label>
                <select id="core_mgt250" name="core_mgt250">
                    <option value="not_yet"     {{ $v('core_mgt250') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                    <option value="in_progress" {{ $v('core_mgt250') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Completed"   {{ $v('core_mgt250') === 'Completed'   ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            {{-- ─── SECTION 6: Marketing ──────────────────────────────────────── --}}
            <h3 class="step-heading">6. Marketing</h3>
            <div class="form-group narrow">
                <label class="field-label" for="core_mkt345">MKT 345 — Marketing Management</label>
                <select id="core_mkt345" name="core_mkt345">
                    <option value="not_yet"     {{ $v('core_mkt345') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                    <option value="in_progress" {{ $v('core_mkt345') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="MKT 345"     {{ $v('core_mkt345') === 'MKT 345'     ? 'selected' : '' }}>MKT 345 (completed)</option>
                </select>
            </div>

            {{-- ─── SECTION 7: Mathematics and Statistics ─────────────────────── --}}
            <h3 class="step-heading">7. Mathematics and Statistics</h3>
            <p class="section-note">
                Level 3 placement requires MATH 110. Levels 1 or 2 fulfill the requirement.
                Finance and Accounting specializations require MATH 111.
            </p>
            <div class="form-grid">

                <div class="form-group">
                    <label class="field-label" for="core_stats">
                        Statistics for Business
                        @if (! $isPost2024)
                            <span class="catalog-badge badge-pre">Pre-2024: ECON 223 or MGT 265</span>
                        @else
                            <span class="catalog-badge badge-post">Post-2024: MGT 265</span>
                        @endif
                    </label>
                    <select id="core_stats" name="core_stats">
                        <option value="not_yet"  {{ $v('core_stats') === 'not_yet'  ? 'selected' : '' }}>Not yet completed</option>
                        <option value="MGT 265"  {{ $v('core_stats') === 'MGT 265'  ? 'selected' : '' }}>MGT 265</option>
                        @if (! $isPost2024)
                            <option value="ECON 223" {{ $v('core_stats') === 'ECON 223' ? 'selected' : '' }}>ECON 223</option>
                        @endif
                    </select>
                </div>

                @if (! $isPost2024)
                    <div class="form-group">
                        <label class="field-label" for="core_mgt365">
                            MGT 365 — Quantitative Methods in Decision Making
                            <span class="catalog-badge badge-pre">Pre-2024 only</span>
                        </label>
                        <select id="core_mgt365" name="core_mgt365">
                            <option value="not_yet"     {{ $v('core_mgt365') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                            <option value="in_progress" {{ $v('core_mgt365') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Completed"   {{ $v('core_mgt365') === 'Completed'   ? 'selected' : '' }}>Completed (MGT 365)</option>
                            <option value="BUS 603"     {{ $v('core_mgt365') === 'BUS 603'     ? 'selected' : '' }}>BUS 603 (equivalent)</option>
                        </select>
                    </div>
                @endif

                <div class="form-group">
                    <label class="field-label" for="core_math">Mathematics</label>
                    <select id="core_math" name="core_math">
                        <option value="not_yet"              {{ $v('core_math') === 'not_yet'              ? 'selected' : '' }}>Not yet completed</option>
                        <option value="MATH 110"             {{ $v('core_math') === 'MATH 110'             ? 'selected' : '' }}>MATH 110 (completed)</option>
                        <option value="MATH 111"             {{ $v('core_math') === 'MATH 111'             ? 'selected' : '' }}>MATH 111 (completed)</option>
                        <option value="Level 1 or 2 Exempt" {{ $v('core_math') === 'Level 1 or 2 Exempt' ? 'selected' : '' }}>Level 1 or 2 Exempt</option>
                    </select>
                </div>

            </div>

            {{-- ─── SECTION 8: Information Management ────────────────────────── --}}
            <h3 class="step-heading">8. Information Management Gateway</h3>
            <p class="section-note">Choose one course not already in your specialization requirements.</p>
            <div class="form-group narrow">
                <label class="field-label" for="core_info_gateway">Info Management Course</label>
                <select id="core_info_gateway" name="core_info_gateway">
                    <option value="not_yet"  {{ $v('core_info_gateway') === 'not_yet'  ? 'selected' : '' }}>Not yet completed</option>
                    @foreach($coreOptions['info_gateway'] ?? [] as $c)
                        <option value="{{ $c }}" {{ $v('core_info_gateway') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            {{-- ─── SECTION 9: Ethics and Law ─────────────────────────────────── --}}
            <h3 class="step-heading">9. Ethics and Law</h3>
            <div class="form-grid">

                <div class="form-group">
                    <label class="field-label" for="core_ethics">Business Ethics (choose one)</label>
                    <select id="core_ethics" name="core_ethics">
                        <option value="not_yet" {{ $v('core_ethics', 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach($coreOptions['ethics'] ?? [] as $c)
                            <option value="{{ $c }}" {{ $v('core_ethics', 'not_yet') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_law">Business Law (choose one)</label>
                    <select id="core_law" name="core_law">
                        <option value="not_yet" {{ $v('core_law') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach($coreOptions['law'] ?? [] as $opt)
                            @php $code = is_array($opt) ? $opt['code'] : $opt; $label = is_array($opt) ? $opt['label'] : $opt; @endphp
                            <option value="{{ $code }}" {{ $v('core_law') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- ─── SECTION 10: Business Strategy and Comprehensive Assessment ── --}}
            <h3 class="step-heading">10. Business Strategy and Comprehensive Assessment</h3>
            <p class="section-note">MGT 475 and BUS 498 must be taken in the same semester (senior year).</p>
            <div class="form-grid">

                <div class="form-group">
                    <label class="field-label" for="core_mgt475">MGT 475 — Business Strategy</label>
                    <select id="core_mgt475" name="core_mgt475">
                        <option value="not_yet"     {{ $v('core_mgt475') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                        <option value="in_progress" {{ $v('core_mgt475') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed"   {{ $v('core_mgt475') === 'Completed'   ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_bus498">BUS 498 — Comprehensive Assessment (0 credits)</label>
                    <select id="core_bus498" name="core_bus498">
                        <option value="not_yet"     {{ $v('core_bus498') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                        <option value="in_progress" {{ $v('core_bus498') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed"   {{ $v('core_bus498') === 'Completed'   ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

            </div>

            {{-- ─── SECTION 11: Career Discernment ─────────────────────────────── --}}
            <h3 class="step-heading">11. Career Discernment</h3>
            <div class="alert-info">
                <strong>Milestone guide:</strong>
                BUS 199 by 30 credits (Fall only) &nbsp;&bull;&nbsp;
                BUS 299A by 60 credits &nbsp;&bull;&nbsp;
                BUS 399A by 90 credits &nbsp;&bull;&nbsp;
                BUS 499A during senior year
            </div>
            <div class="form-grid">

                <div class="form-group">
                    <label class="field-label" for="core_bus199">BUS 199 — Career Discernment I (Fall Only)</label>
                    <select id="core_bus199" name="core_bus199">
                        <option value="not_yet"     {{ $v('core_bus199') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                        <option value="in_progress" {{ $v('core_bus199') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed"   {{ $v('core_bus199') === 'Completed'   ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_bus299a">BUS 299A — Career Discernment II{{ $isSales ? ' / MKT 299' : '' }}</label>
                    <select id="core_bus299a" name="core_bus299a">
                        <option value="not_yet"     {{ $v('core_bus299a') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                        <option value="in_progress" {{ $v('core_bus299a') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="BUS 299A"    {{ $v('core_bus299a') === 'BUS 299A'    ? 'selected' : '' }}>BUS 299A (completed)</option>
                        @if ($isSales)
                            <option value="MKT 299" {{ $v('core_bus299a') === 'MKT 299' ? 'selected' : '' }}>MKT 299 — Sales students (completed)</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_bus399a">BUS 399A — Career Discernment III{{ $isSales ? ' / MKT 399' : '' }}</label>
                    <select id="core_bus399a" name="core_bus399a">
                        <option value="not_yet"     {{ $v('core_bus399a') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                        <option value="in_progress" {{ $v('core_bus399a') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="BUS 399A"    {{ $v('core_bus399a') === 'BUS 399A'    ? 'selected' : '' }}>BUS 399A (completed)</option>
                        @if ($isSales)
                            <option value="MKT 399" {{ $v('core_bus399a') === 'MKT 399' ? 'selected' : '' }}>MKT 399 — Sales students (completed)</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_bus499a">BUS 499A — Career Discernment IV{{ $isSales ? ' / MKT 499' : '' }}</label>
                    <select id="core_bus499a" name="core_bus499a">
                        <option value="not_yet"     {{ $v('core_bus499a') === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                        <option value="in_progress" {{ $v('core_bus499a') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="BUS 499A"    {{ $v('core_bus499a') === 'BUS 499A'    ? 'selected' : '' }}>BUS 499A (completed)</option>
                        @if ($isSales)
                            <option value="MKT 499" {{ $v('core_bus499a') === 'MKT 499' ? 'selected' : '' }}>MKT 499 — Sales students (completed)</option>
                        @endif
                    </select>
                </div>

            </div>

            {{-- ─── SECTION 12: Business Electives (Pre-2024 single spec only) ── --}}
            @if ($showElectives)
                <h3 class="step-heading">12. Business Electives</h3>
                <p class="section-note">
                    Two additional business or economics elective courses are required for single-specialization Pre-2024 students.
                    Enter the course code (e.g. MGT 302). Not required if you have a double specialization.
                </p>
                <div class="form-grid">

                    <div class="form-group">
                        <label class="field-label" for="core_elective_1">Business Elective 1</label>
                        <input
                            type="text"
                            id="core_elective_1"
                            name="core_elective_1"
                            class="text-input{{ $errors->has('core_elective_1') ? ' input-error' : '' }}"
                            value="{{ old('core_elective_1', $data['core_elective_1'] ?? '') }}"
                            placeholder="e.g. MGT 302"
                            autocomplete="off"
                            data-error-msg="Must be a BUS, MGT, MKT, FIN, ACCT, ECON, ENT, or SRES course."
                        >
                        <p class="field-hint">Any BUS, MGT, MKT, FIN, ACCT, ECON, ENT, or SRES course</p>
                        <p class="field-error" id="err_core_elective_1">@error('core_elective_1'){{ $message }}@enderror</p>
                    </div>

                    <div class="form-group">
                        <label class="field-label" for="core_elective_2">Business Elective 2</label>
                        <input
                            type="text"
                            id="core_elective_2"
                            name="core_elective_2"
                            class="text-input{{ $errors->has('core_elective_2') ? ' input-error' : '' }}"
                            value="{{ old('core_elective_2', $data['core_elective_2'] ?? '') }}"
                            placeholder="e.g. ECON 301"
                            autocomplete="off"
                            data-error-msg="Must be a BUS, MGT, MKT, FIN, ACCT, ECON, ENT, or SRES course."
                        >
                        <p class="field-hint">Any BUS, MGT, MKT, FIN, ACCT, ECON, ENT, or SRES course</p>
                        <p class="field-error" id="err_core_elective_2">@error('core_elective_2'){{ $message }}@enderror</p>
                    </div>

                </div>
            @endif

            {{-- ─── TRANSFER CREDITS (Optional) ─────────────────────────────── --}}
            <h3 class="step-heading">Transfer Credits <span style="font-family:'Roboto',sans-serif; font-size:0.8rem; font-weight:400; color:#777;">(optional)</span></h3>
            <p class="section-note">
                List any transfer credits you are applying toward your degree. These will appear on your academic profile so the advisor can factor them into your plan.
            </p>

            <table class="transfer-table" id="transfer-table">
                <thead>
                    <tr>
                        <th style="width:22%">Institution</th>
                        <th style="width:24%">Original Course Name</th>
                        <th style="width:20%">CUA Equivalent</th>
                        <th style="width:10%">Credits</th>
                        <th style="width:10%">Grade</th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody id="transfer-rows"></tbody>
            </table>
            <button type="button" class="btn-add-row" id="btn-add-transfer" onclick="addTransferRow()">+ Add Transfer Credit</button>

            {{-- Hidden template for cloning --}}
            <template id="transfer-row-tpl">
                <tr>
                    <td><input type="text" name="transfers[__IDX__][institution]" placeholder="e.g. Georgetown Univ." autocomplete="off"></td>
                    <td><input type="text" name="transfers[__IDX__][orig_name]" placeholder="e.g. Introduction to Finance" autocomplete="off"></td>
                    <td><input type="text" name="transfers[__IDX__][cua_equiv]" placeholder="e.g. FIN 226" autocomplete="off"></td>
                    <td><input type="number" name="transfers[__IDX__][credits]" placeholder="3" min="0" max="12" step="0.5"></td>
                    <td><input type="text" name="transfers[__IDX__][grade]" placeholder="A, B+" maxlength="3" autocomplete="off"></td>
                    <td><button type="button" class="btn-remove" onclick="removeTransferRow(this)" title="Remove row">×</button></td>
                </tr>
            </template>

            <div class="form-actions">
                <a href="{{ route('onboarding.step', 3) }}" class="btn-secondary">← Back</a>
                <button type="submit" class="btn-primary">{{ $degree === 'bs_accounting' ? 'Next: Accounting Requirements →' : 'Next: Specialization Courses →' }}</button>
            </div>

        </form>
    </div>

    @if ($showElectives)
    <script>
        var ELECTIVE_PREFIXES = ['BUS ', 'MGT ', 'MKT ', 'FIN ', 'ACCT ', 'ECON ', 'ENT ', 'SRES '];

        function validateElectiveInput(inputId, errorId) {
            var input = document.getElementById(inputId);
            var errorEl = document.getElementById(errorId);
            if (!input || !errorEl) { return; }
            input.addEventListener('input', function () {
                var val = this.value.trim().toUpperCase();
                if (val === '') {
                    errorEl.textContent = '';
                    this.classList.remove('input-error', 'input-valid');
                    return;
                }
                var valid = ELECTIVE_PREFIXES.some(function (p) { return val.indexOf(p) === 0; });
                if (valid) {
                    errorEl.textContent = '';
                    this.classList.remove('input-error');
                    this.classList.add('input-valid');
                } else {
                    errorEl.textContent = this.dataset.errorMsg;
                    this.classList.add('input-error');
                    this.classList.remove('input-valid');
                }
            });
        }

        validateElectiveInput('core_elective_1', 'err_core_elective_1');
        validateElectiveInput('core_elective_2', 'err_core_elective_2');
    </script>
    @endif

    <script>
        var transferRowCount = 0;
        var MAX_TRANSFER_ROWS = 10;

        function addTransferRow() {
            if (transferRowCount >= MAX_TRANSFER_ROWS) {
                document.getElementById('btn-add-transfer').disabled = true;
                return;
            }
            var tpl = document.getElementById('transfer-row-tpl');
            var clone = tpl.content.cloneNode(true);
            var idx = transferRowCount;
            clone.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace('__IDX__', idx);
            });
            document.getElementById('transfer-rows').appendChild(clone);
            transferRowCount++;
            if (transferRowCount >= MAX_TRANSFER_ROWS) {
                document.getElementById('btn-add-transfer').disabled = true;
            }
        }

        function removeTransferRow(btn) {
            btn.closest('tr').remove();
            transferRowCount = Math.max(0, transferRowCount - 1);
            document.getElementById('btn-add-transfer').disabled = false;
        }
    </script>

</body>
</html>
