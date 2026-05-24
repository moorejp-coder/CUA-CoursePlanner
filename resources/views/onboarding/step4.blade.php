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
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--cua-navy);
            margin: 1.5rem 0 0.75rem;
            padding-bottom: 0.4rem;
            border-bottom: 2px solid var(--cua-gold);
        }

        .step-heading:first-of-type { margin-top: 0; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 520px) {
            .form-grid { grid-template-columns: 1fr; }
        }

        .form-group { margin-bottom: 0.75rem; }

        label.field-label {
            display: block;
            font-weight: 500;
            font-size: 0.88rem;
            margin-bottom: 0.35rem;
            color: #333;
        }

        select {
            width: 100%;
            padding: 0.55rem 0.8rem;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            font-size: 0.93rem;
            font-family: 'Roboto', sans-serif;
            transition: border-color 0.2s;
            background: #fff;
        }

        select:focus {
            outline: none;
            border-color: var(--cua-navy);
            box-shadow: 0 0 0 2px rgba(0,51,102,0.12);
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
            margin: 0 0 1rem;
            font-size: 0.88rem;
            color: var(--cua-dark);
        }
    </style>
</head>
<body>

    @include('onboarding.partials.header', ['step' => $step, 'totalSteps' => $totalSteps])

    @php
        $catalogYear = $data['catalog_year'] ?? 'post_2024';
        $isPost2024  = $catalogYear === 'post_2024';
        $isSales     = in_array('sales', [
            $data['specialization_1'] ?? '',
            $data['specialization_2'] ?? '',
            $data['specialization_3'] ?? '',
        ]);

        $v = fn(string $key, string $default = 'not_yet') => old($key, $data[$key] ?? $default);
    @endphp

    <div class="wizard-card">

        @if ($errors->any())
            <div class="alert-warning">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('onboarding.save', 4) }}">
            @csrf

            {{-- Freshman / Sophomore Core --}}
            <h3 class="step-heading">Freshman &amp; Sophomore Core</h3>

            <div class="form-grid">

                <div class="form-group">
                    <label class="field-label" for="core_ent118">ENT 118 — Business Foundations I</label>
                    <select id="core_ent118" name="core_ent118">
                        <option value="not_yet" {{ $v('core_ent118') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="ENT 118A" {{ $v('core_ent118') === 'ENT 118A' ? 'selected' : '' }}>ENT 118A</option>
                        <option value="ENT 118B" {{ $v('core_ent118') === 'ENT 118B' ? 'selected' : '' }}>ENT 118B</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_mgt123">MGT 123 — Business Foundations II</label>
                    <select id="core_mgt123" name="core_mgt123">
                        <option value="not_yet" {{ $v('core_mgt123') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="MGT 123A" {{ $v('core_mgt123') === 'MGT 123A' ? 'selected' : '' }}>MGT 123A</option>
                        <option value="MGT 123B" {{ $v('core_mgt123') === 'MGT 123B' ? 'selected' : '' }}>MGT 123B</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_sres101">SRES 101 / Microeconomics</label>
                    <select id="core_sres101" name="core_sres101">
                        <option value="not_yet" {{ $v('core_sres101') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="SRES 101" {{ $v('core_sres101') === 'SRES 101' ? 'selected' : '' }}>SRES 101</option>
                        <option value="ECON 100" {{ $v('core_sres101') === 'ECON 100' ? 'selected' : '' }}>ECON 100</option>
                        <option value="ECON 102" {{ $v('core_sres101') === 'ECON 102' ? 'selected' : '' }}>ECON 102</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_sres102">SRES 102 / Macroeconomics</label>
                    <select id="core_sres102" name="core_sres102">
                        <option value="not_yet" {{ $v('core_sres102') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="SRES 102" {{ $v('core_sres102') === 'SRES 102' ? 'selected' : '' }}>SRES 102</option>
                        <option value="ECON 200" {{ $v('core_sres102') === 'ECON 200' ? 'selected' : '' }}>ECON 200</option>
                        <option value="ECON 101" {{ $v('core_sres102') === 'ECON 101' ? 'selected' : '' }}>ECON 101</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_acct205">ACCT 205 — Financial Accounting</label>
                    <select id="core_acct205" name="core_acct205">
                        <option value="not_yet" {{ $v('core_acct205') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="Completed" {{ $v('core_acct205') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_acct206">ACCT 206 — Managerial Accounting</label>
                    <select id="core_acct206" name="core_acct206">
                        <option value="not_yet" {{ $v('core_acct206') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="Completed" {{ $v('core_acct206') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_fin226">FIN 226 — Corporate Finance</label>
                    <select id="core_fin226" name="core_fin226">
                        <option value="not_yet" {{ $v('core_fin226') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="Completed" {{ $v('core_fin226') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_math">Mathematics</label>
                    <select id="core_math" name="core_math">
                        <option value="not_yet" {{ $v('core_math') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="MATH 110" {{ $v('core_math') === 'MATH 110' ? 'selected' : '' }}>MATH 110 (completed)</option>
                        <option value="MATH 111" {{ $v('core_math') === 'MATH 111' ? 'selected' : '' }}>MATH 111 (completed)</option>
                        <option value="MATH 110+111" {{ $v('core_math') === 'MATH 110+111' ? 'selected' : '' }}>MATH 110 + 111 (both)</option>
                        <option value="EXEMPT Level 1" {{ $v('core_math') === 'EXEMPT Level 1' ? 'selected' : '' }}>EXEMPT — Level 1</option>
                        <option value="EXEMPT Level 2" {{ $v('core_math') === 'EXEMPT Level 2' ? 'selected' : '' }}>EXEMPT — Level 2</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_bus199">BUS 199 — Career Discernment I</label>
                    <select id="core_bus199" name="core_bus199">
                        <option value="not_yet" {{ $v('core_bus199') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="Completed" {{ $v('core_bus199') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_bus299a">BUS 299A — Career Discernment II{{ $isSales ? ' / MKT 299' : '' }}</label>
                    <select id="core_bus299a" name="core_bus299a">
                        <option value="not_yet" {{ $v('core_bus299a') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="BUS 299A" {{ $v('core_bus299a') === 'BUS 299A' ? 'selected' : '' }}>BUS 299A</option>
                        @if ($isSales)
                            <option value="MKT 299" {{ $v('core_bus299a') === 'MKT 299' ? 'selected' : '' }}>MKT 299 (Sales students)</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_mgt250">MGT 250 — Business Communication</label>
                    <select id="core_mgt250" name="core_mgt250">
                        <option value="not_yet" {{ $v('core_mgt250') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="Completed" {{ $v('core_mgt250') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                @if ($isPost2024)
                    <div class="form-group">
                        <label class="field-label" for="core_sres290">SRES 290 — Catholic Social Thought in Business</label>
                        <select id="core_sres290" name="core_sres290">
                            <option value="not_yet" {{ $v('core_sres290') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            <option value="Completed" {{ $v('core_sres290') === 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                @endif

            </div>{{-- end form-grid --}}

            {{-- Junior / Senior Core --}}
            <h3 class="step-heading">Junior &amp; Senior Core</h3>

            <div class="form-grid">

                <div class="form-group">
                    <label class="field-label" for="core_stats">Statistics</label>
                    <select id="core_stats" name="core_stats">
                        <option value="not_yet" {{ $v('core_stats') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="ECON 223" {{ $v('core_stats') === 'ECON 223' ? 'selected' : '' }}>ECON 223</option>
                        <option value="MGT 265" {{ $v('core_stats') === 'MGT 265' ? 'selected' : '' }}>MGT 265</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_info_gateway">Info Management Gateway</label>
                    <select id="core_info_gateway" name="core_info_gateway">
                        <option value="not_yet" {{ $v('core_info_gateway') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['MGT 331','MGT 347','MGT 332','DA 124','MGT 240','MGT 351','MGT 361'] as $c)
                            <option value="{{ $c }}" {{ $v('core_info_gateway') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_mkt345">MKT 345 — Marketing Management</label>
                    <select id="core_mkt345" name="core_mkt345">
                        <option value="not_yet" {{ $v('core_mkt345') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="MKT 345" {{ $v('core_mkt345') === 'MKT 345' ? 'selected' : '' }}>MKT 345</option>
                        <option value="BUS 604" {{ $v('core_mkt345') === 'BUS 604' ? 'selected' : '' }}>BUS 604</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_mgt301">MGT 301 — Operations Management</label>
                    <select id="core_mgt301" name="core_mgt301">
                        <option value="not_yet" {{ $v('core_mgt301') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="Completed" {{ $v('core_mgt301') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_law">Law &amp; Ethics Elective</label>
                    <select id="core_law" name="core_law">
                        <option value="not_yet" {{ $v('core_law') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['BUS 301','BUS 401','MGT 353','POLS 321','MGT 321','MGT 322','MGT 371','MGT 411'] as $c)
                            <option value="{{ $c }}" {{ $v('core_law') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                @if (!$isPost2024)
                    <div class="form-group">
                        <label class="field-label" for="core_mgt365">MGT 365 — Strategic Management (Pre-2024)</label>
                        <select id="core_mgt365" name="core_mgt365">
                            <option value="not_yet" {{ $v('core_mgt365') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            <option value="MGT 365" {{ $v('core_mgt365') === 'MGT 365' ? 'selected' : '' }}>MGT 365</option>
                            <option value="BUS 603" {{ $v('core_mgt365') === 'BUS 603' ? 'selected' : '' }}>BUS 603</option>
                        </select>
                    </div>
                @endif

                <div class="form-group">
                    <label class="field-label" for="core_bus399a">BUS 399A — Career Discernment III{{ $isSales ? ' / MKT 399' : '' }}</label>
                    <select id="core_bus399a" name="core_bus399a">
                        <option value="not_yet" {{ $v('core_bus399a') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="BUS 399A" {{ $v('core_bus399a') === 'BUS 399A' ? 'selected' : '' }}>BUS 399A</option>
                        @if ($isSales)
                            <option value="MKT 399" {{ $v('core_bus399a') === 'MKT 399' ? 'selected' : '' }}>MKT 399 (Sales students)</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_bus499a">BUS 499A — Career Discernment IV{{ $isSales ? ' / MKT 499' : '' }}</label>
                    <select id="core_bus499a" name="core_bus499a">
                        <option value="not_yet" {{ $v('core_bus499a') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="BUS 499A" {{ $v('core_bus499a') === 'BUS 499A' ? 'selected' : '' }}>BUS 499A</option>
                        @if ($isSales)
                            <option value="MKT 499" {{ $v('core_bus499a') === 'MKT 499' ? 'selected' : '' }}>MKT 499 (Sales students)</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_mgt475">MGT 475 — Business &amp; Society (Capstone)</label>
                    <select id="core_mgt475" name="core_mgt475">
                        <option value="not_yet" {{ $v('core_mgt475') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="Completed" {{ $v('core_mgt475') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="core_bus498">BUS 498 — Senior Capstone</label>
                    <select id="core_bus498" name="core_bus498">
                        <option value="not_yet" {{ $v('core_bus498') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="Completed" {{ $v('core_bus498') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

            </div>{{-- end form-grid --}}

            {{-- Career Discernment reminder --}}
            <div class="alert-info" style="margin-top: 1.25rem;">
                <strong>Career Discernment Milestone Guide:</strong>
                BUS 199 by 30 credits &nbsp;&bull;&nbsp;
                BUS 299A by 60 credits &nbsp;&bull;&nbsp;
                BUS 399A by 90 credits &nbsp;&bull;&nbsp;
                BUS 499A during senior year
            </div>

            <div class="form-actions">
                <a href="{{ route('onboarding.step', 3) }}" class="btn-secondary">← Back</a>
                <button type="submit" class="btn-primary">Next: Specialization Courses →</button>
            </div>

        </form>
    </div>

</body>
</html>
