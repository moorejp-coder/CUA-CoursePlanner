<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Profile Setup — Step 3 | Busch School</title>

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

        .form-group { margin-bottom: 0.25rem; }

        label.field-label {
            display: block;
            font-weight: 500;
            font-size: 0.88rem;
            margin-bottom: 0.35rem;
            color: #333;
        }

        .optional-tag {
            font-size: 0.75rem;
            color: #999;
            font-weight: 400;
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

        .filter-input {
            width: 100%;
            padding: 0.4rem 0.7rem;
            border: 1.5px solid #ccc;
            border-bottom: none;
            border-radius: 6px 6px 0 0;
            font-size: 0.82rem;
            font-family: 'Roboto', sans-serif;
            transition: border-color 0.2s;
            background: #fafafa;
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--cua-navy);
            box-shadow: 0 0 0 2px rgba(0,51,102,0.12);
        }

        .filter-input + select {
            border-radius: 0 0 6px 6px;
        }

        .field-hint {
            font-size: 0.78rem;
            color: #777;
            margin: 0.3rem 0 0;
            line-height: 1.4;
        }

        .field-error {
            font-size: 0.8rem;
            color: var(--cua-red);
            margin: 0.3rem 0 0;
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

        .alert-info {
            background: #e8f0fe;
            border-left: 4px solid var(--cua-navy);
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin: 0 0 1.5rem;
            font-size: 0.9rem;
            color: var(--cua-dark);
        }

        .alert-warning {
            background: #fffbea;
            border-left: 4px solid var(--cua-gold);
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin: 0.75rem 0;
            font-size: 0.9rem;
        }

        .math-field-wrap {
            max-width: 320px;
        }

        .math-note {
            font-size: 0.82rem;
            color: #555;
            margin: 0.5rem 0 0.75rem;
        }

        /* Auto-fill styles */
        .autofill-badge {
            display: inline-block;
            background: var(--cua-gold);
            color: #5c3d00;
            font-size: 0.68rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 0.4rem;
            vertical-align: middle;
            letter-spacing: 0.02em;
        }

        select.autofill-border {
            border-color: var(--cua-gold) !important;
            box-shadow: 0 0 0 2px rgba(201,168,76,0.25);
        }

        .autofill-note {
            font-size: 0.8rem;
            color: #6b4d00;
            background: #fffbea;
            border-left: 3px solid var(--cua-gold);
            padding: 0.3rem 0.6rem;
            border-radius: 3px;
            margin-top: 0.3rem;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    @include('onboarding.partials.header', ['step' => $step, 'totalSteps' => $totalSteps])

    @php
        $natSciOptions = [
            'ANTH 105' => 'ANTH 105', 'ANTH 108' => 'ANTH 108',
            'ANTH 204' => 'ANTH 204', 'ANTH 206' => 'ANTH 206',
            'ANTH 352' => 'ANTH 352', 'ANTH 354' => 'ANTH 354',
            'BIOL 103' => 'BIOL 103', 'BIOL 109' => 'BIOL 109',
            'CHEM 10'  => 'CHEM 10',  'CHEM 110' => 'CHEM 110',
            'CHEM 125' => 'CHEM 125', 'CHEM 126' => 'CHEM 126',
            'CHEM 127' => 'CHEM 127', 'CHEM 128R' => 'CHEM 128R',
            'CHEM 130' => 'CHEM 130',
            'PHYS 101' => 'PHYS 101', 'PHYS 103' => 'PHYS 103',
            'PHYS 122' => 'PHYS 122', 'PHYS 206' => 'PHYS 206',
            'PHYS 215H' => 'PHYS 215H',
            'PSY 204'  => 'PSY 204',  'SAS 225'  => 'SAS 225',
            'HSEV 101' => 'HSEV 101 (Honors Only)',
        ];

        $litOptions = [
            'ARAB 279','CLAS 105','CLAS 106','CLAS 211','CLAS 212R',
            'CLAS 244','CLAS 251','CLAS 261',
            'ENG 206','ENG 212','ENG 231','ENG 232','ENG 235','ENG 236',
            'ENG 278','ENG 305','ENG 306','ENG 312','ENG 341','ENG 345',
            'ENG 347','ENG 351','ENG 352','ENG 356','ENG 364','ENG 369',
            'ENG 376','ENG 378-R','ENG 379','ENG 461','ENG 462',
            'FREN 220','FREN 230','FREN 242','FREN 279',
            'GER 220','GER 225','GER 230','GER 255',
            'GS 220','HUM 101','HUM 124',
            'ITAL 212','ITAL 220','ITAL 226','ITAL 232',
            'MDIA 225',
            'SPAN 224','SPAN 225','SPAN 240','SPAN 321',
            'HSHU 203','HSLS 353',
        ];

        $fineArtsOptions = [
            'ARPL 211',
            'ART 201','ART 211','ART 212','ART 213','ART 222',
            'ART 251','ART 252','ART 272','ART 302','ART 308',
            'ART 317','ART 318','ART 319','ART 320','ART 335',
            'CLAS 214','CLAS 221','CLAS 251','CLAS 261',
            'CLAS 317','CLAS 318','CLAS 318R',
            'DR 105','DR 106','DR 110','DR 201','DR 202',
            'DR 207','DR 305','DR 403',
            'DNCE 101',
            'ENG 300','ENG 302',
            'ENGR 101',
            'HIST 390A',
            'ITAL 219-R',
            'MDIA 201','MDIA 343',
            'MUS 110','MUS 112','MUS 131','MUS 134','MUS 135',
            'MUS 178','MUS 276','MUS 304','MUS 327','MUS 328','MUS 328H',
            'HSLS 352','HSAM 101',
        ];

        $socialSciOptions = [
            'ANTH 101','ANTH 110','ANTH 201','ANTH 203','ANTH 211',
            'ANTH 226','ANTH 240','ANTH 260',
            'CEE 201',
            'ECON 100','ECON 101','ECON 102','ECON 103','ECON 104','ECON 200',
            'GS 101',
            'PSY 201','PSY 226','PSY 261',
            'SOC 101','SOC 102','SOC 102H','SOC 202','SOC 206',
            'SOC 210','SOC 281','SOC 330','SOC 358','SOC 358H',
            'SRES 101','SRES 102','SRES 345',
            'SSS 101','SSS 226',
            'HSEV 203','HSSS 101','HSSS 102','HSSS 204',
        ];

        $histPolOptions = [
            'ANTH 215',
            'CLAS 205','CLAS 206','CLAS 206R','CLAS 207','CLAS 220',
            'CLAS 226','CLAS 260','CLAS 304','CLAS 308',
            'ECST 315',
            'EURO 203',
            'HIST 140','HIST 142','HIST 151','HIST 202','HIST 205',
            'HIST 206','HIST 206R','HIST 208','HIST 222','HIST 224',
            'HIST 226','HIST 229','HIST 231A','HIST 231B','HIST 235',
            'HIST 246','HIST 257','HIST 258','HIST 301','HIST 308B',
            'HIST 309','HIST 309B','HIST 312','HIST 315','HIST 316',
            'HIST 334A','HIST 349','HIST 351','HIST 371D','HIST 380D',
            'HIST 384A','HIST 385',
            'ITAL 221',
            'MDIA 202',
            'POL 111','POL 112','POL 211','POL 226',
            'WASH 101',
            'HSHU 101','HSHU 102','HSHU 204',
            'HSLS 205','HSLS 351','HSLS 354',
        ];

        $mathOptions = [
            'MATH 111','MATH 112','MATH 114','MATH 121','MATH 122',
            'MATH 168','MATH 175','MATH 187',
            'HSMS 230','HSMS 330','HSSS 203',
            'math_exempt',
        ];

        // Social Science auto-fill from Economic Thought (SRES/ECON in business core)
        $ssAutoFill  = $socialScienceAutoFill ?? null;
        $ssValue     = $ssAutoFill['value'] ?? null;
        $ssNote      = $ssAutoFill['note'] ?? null;
        $ssActive    = $ssAutoFill['active'] ?? false;

        $sessionSS = $data['la_social_science'] ?? null;
        $oldSS     = old('la_social_science');

        if ($oldSS !== null) {
            $currentSS      = $oldSS;
            $showSsAutoFill = $ssActive && $oldSS === $ssValue;
        } elseif ($ssActive && ($sessionSS === null || $sessionSS === '' || $sessionSS === 'not_yet')) {
            $currentSS      = $ssValue;
            $showSsAutoFill = true;
        } else {
            $currentSS      = $sessionSS ?? 'not_yet';
            $showSsAutoFill = $ssActive && $currentSS === $ssValue;
        }
    @endphp

    <div class="wizard-card">

        <div class="alert-info">
            Select the courses you have <strong>already completed</strong>. Choose "Not yet completed" for requirements still in progress.
        </div>

        <form method="POST" action="{{ route('onboarding.save', 3) }}">
            @csrf

            {{-- Core Liberal Arts --}}
            <h3 class="step-heading">Core Liberal Arts Requirements</h3>

            <div class="form-grid">

                {{-- Classical Philosophy --}}
                <div class="form-group">
                    <label class="field-label" for="la_classical_philosophy">Classical Philosophy</label>
                    <select id="la_classical_philosophy" name="la_classical_philosophy">
                        <option value="not_yet" {{ old('la_classical_philosophy', $data['la_classical_philosophy'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['PHIL 201','PHIL 211','HSPH 101'] as $c)
                            <option value="{{ $c }}" {{ old('la_classical_philosophy', $data['la_classical_philosophy'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Modern Philosophy --}}
                <div class="form-group">
                    <label class="field-label" for="la_modern_philosophy">Modern Philosophy</label>
                    <select id="la_modern_philosophy" name="la_modern_philosophy">
                        <option value="not_yet" {{ old('la_modern_philosophy', $data['la_modern_philosophy'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['PHIL 202','PHIL 212','HSPH 102'] as $c)
                            <option value="{{ $c }}" {{ old('la_modern_philosophy', $data['la_modern_philosophy'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Theology I --}}
                <div class="form-group">
                    <label class="field-label" for="la_theology_1">Theology I</label>
                    <select id="la_theology_1" name="la_theology_1">
                        <option value="not_yet" {{ old('la_theology_1', $data['la_theology_1'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['TRS 201','HSTR 101'] as $c)
                            <option value="{{ $c }}" {{ old('la_theology_1', $data['la_theology_1'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Theology II --}}
                <div class="form-group">
                    <label class="field-label" for="la_theology_2">Theology II</label>
                    <select id="la_theology_2" name="la_theology_2">
                        <option value="not_yet" {{ old('la_theology_2', $data['la_theology_2'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['TRS 202A','TRS 202B','HSTR (any)'] as $c)
                            <option value="{{ $c }}" {{ old('la_theology_2', $data['la_theology_2'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Rhetoric & Composition --}}
                <div class="form-group">
                    <label class="field-label" for="la_rhetoric">Rhetoric &amp; Composition</label>
                    <select id="la_rhetoric" name="la_rhetoric">
                        <option value="not_yet" {{ old('la_rhetoric', $data['la_rhetoric'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        <option value="ENG 101"  {{ old('la_rhetoric', $data['la_rhetoric'] ?? '') === 'ENG 101'  ? 'selected' : '' }}>ENG 101</option>
                        <option value="ENG 101H" {{ old('la_rhetoric', $data['la_rhetoric'] ?? '') === 'ENG 101H' ? 'selected' : '' }}>ENG 101H (Honors Only)</option>
                        <option value="ENG 101C" {{ old('la_rhetoric', $data['la_rhetoric'] ?? '') === 'ENG 101C' ? 'selected' : '' }}>ENG 101C (Cornerstone Only)</option>
                    </select>
                </div>

                {{-- Foundations in Natural Science (searchable) --}}
                <div class="form-group">
                    <label class="field-label" for="select-natural-science">Foundations in Natural Science</label>
                    <input type="text" id="filter-natural-science" class="filter-input" placeholder="Type to filter..." autocomplete="off">
                    <select id="select-natural-science" name="la_natural_science">
                        <option value="not_yet" {{ old('la_natural_science', $data['la_natural_science'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach($natSciOptions as $val => $label)
                            <option value="{{ $val }}" {{ old('la_natural_science', $data['la_natural_science'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Explorations in Literature (searchable) --}}
                <div class="form-group">
                    <label class="field-label" for="select-literature">Explorations in Literature</label>
                    <input type="text" id="filter-literature" class="filter-input" placeholder="Type to filter..." autocomplete="off">
                    <select id="select-literature" name="la_literature">
                        <option value="not_yet" {{ old('la_literature', $data['la_literature'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach($litOptions as $c)
                            <option value="{{ $c }}" {{ old('la_literature', $data['la_literature'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Explorations in Fine Arts (searchable) --}}
                <div class="form-group">
                    <label class="field-label" for="select-fine-arts">Explorations in Fine Arts</label>
                    <input type="text" id="filter-fine-arts" class="filter-input" placeholder="Type to filter..." autocomplete="off">
                    <select id="select-fine-arts" name="la_fine_arts">
                        <option value="not_yet" {{ old('la_fine_arts', $data['la_fine_arts'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach($fineArtsOptions as $c)
                            <option value="{{ $c }}" {{ old('la_fine_arts', $data['la_fine_arts'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Foundations in Social Science (searchable, auto-fill from SRES/ECON) --}}
                <div class="form-group"
                     x-data="{ autofilled: {{ $showSsAutoFill ? 'true' : 'false' }}, autoFillValue: '{{ e($ssValue ?? '') }}' }">
                    <label class="field-label" for="select-social-science">
                        Foundations in Social Science
                        <span x-show="autofilled" class="autofill-badge" {{ $showSsAutoFill ? '' : 'style="display:none;"' }}>Auto-filled from Economic Thought</span>
                    </label>
                    <input type="text" id="filter-social-science" class="filter-input" placeholder="Type to filter..." autocomplete="off">
                    <input type="hidden" name="la_social_science_autofilled" :value="autofilled ? '1' : '0'" value="{{ $showSsAutoFill ? '1' : '0' }}">
                    <select id="select-social-science" name="la_social_science"
                            :class="{ 'autofill-border': autofilled }"
                            @change="autofilled = ($event.target.value === autoFillValue && autoFillValue !== '')">
                        <option value="not_yet" {{ $currentSS === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach($socialSciOptions as $c)
                            <option value="{{ $c }}" {{ $currentSS === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                    @if($ssNote)
                    <p x-show="autofilled" class="autofill-note" {{ $showSsAutoFill ? '' : 'style="display:none;"' }}>{{ $ssNote }}</p>
                    @endif
                </div>

                {{-- Foundations in History or Politics (searchable) --}}
                <div class="form-group">
                    <label class="field-label" for="select-history-politics">Foundations in History or Politics</label>
                    <input type="text" id="filter-history-politics" class="filter-input" placeholder="Type to filter..." autocomplete="off">
                    <select id="select-history-politics" name="la_history_politics">
                        <option value="not_yet" {{ old('la_history_politics', $data['la_history_politics'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach($histPolOptions as $c)
                            <option value="{{ $c }}" {{ old('la_history_politics', $data['la_history_politics'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Language I --}}
                <div class="form-group">
                    <label class="field-label" for="la_language_1">Language I</label>
                    <select id="la_language_1" name="la_language_1">
                        <option value="not_yet" {{ old('la_language_1', $data['la_language_1'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['ARAB 103','CHN 103','FREN 103','GER 103','GR 103','IRSH 103','ITAL 103','LAT 103','SPAN 103','SPAN 111','SPAN 113'] as $c)
                            <option value="{{ $c }}" {{ old('la_language_1', $data['la_language_1'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Language II --}}
                <div class="form-group">
                    <label class="field-label" for="la_language_2">Language II</label>
                    <select id="la_language_2" name="la_language_2">
                        <option value="not_yet" {{ old('la_language_2', $data['la_language_2'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['ARAB 104','CHN 104','FREN 104','GER 104','GR 104','IRSH 104','ITAL 104','LAT 104','SPAN 104'] as $c)
                            <option value="{{ $c }}" {{ old('la_language_2', $data['la_language_2'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

            </div>{{-- end .form-grid (core) --}}

            {{-- Additional Requirements --}}
            <h3 class="step-heading">Additional Requirements</h3>

            <div class="form-grid">

                {{-- Philosophy Elective (text input) --}}
                <div class="form-group">
                    <label class="field-label" for="la_phil_elective">
                        Philosophy Elective <span class="optional-tag">(optional)</span>
                    </label>
                    <input
                        type="text"
                        id="la_phil_elective"
                        name="la_phil_elective"
                        class="text-input{{ $errors->has('la_phil_elective') ? ' input-error' : '' }}"
                        value="{{ old('la_phil_elective', $data['la_phil_elective'] ?? '') }}"
                        placeholder="e.g. PHIL 301"
                        autocomplete="off"
                        data-error-msg="Must be a PHIL course (e.g. PHIL 301), HSPH 203, or HSPH 204."
                    >
                    <p class="field-hint">Enter a PHIL course code (e.g. PHIL 301). Honors students may enter HSPH 203 or HSPH 204.</p>
                    <p class="field-error" id="err_la_phil_elective">@error('la_phil_elective'){{ $message }}@enderror</p>
                </div>

                {{-- Theology Elective (text input) --}}
                <div class="form-group">
                    <label class="field-label" for="la_theology_elective">
                        Theology Elective <span class="optional-tag">(optional)</span>
                    </label>
                    <input
                        type="text"
                        id="la_theology_elective"
                        name="la_theology_elective"
                        class="text-input{{ $errors->has('la_theology_elective') ? ' input-error' : '' }}"
                        value="{{ old('la_theology_elective', $data['la_theology_elective'] ?? '') }}"
                        placeholder="e.g. TRS 301"
                        autocomplete="off"
                        data-error-msg="Must be a TRS course, any HSTR course, or HSEV 102."
                    >
                    <p class="field-hint">Enter a TRS course code (e.g. TRS 301). Honors students may enter any HSTR course or HSEV 102.</p>
                    <p class="field-error" id="err_la_theology_elective">@error('la_theology_elective'){{ $message }}@enderror</p>
                </div>

            </div>{{-- end .form-grid (additional) --}}

            {{-- Foundations in Mathematical Thinking --}}
            <h3 class="step-heading">Foundations in Mathematical Thinking</h3>
            <p class="math-note">Math placement may satisfy this requirement. Select the course you completed or "Math Placement Exempt" if you placed out.</p>

            <div class="math-field-wrap">
                <label class="field-label" for="la_math_thinking">Math Thinking Course</label>
                <select id="la_math_thinking" name="la_math_thinking">
                    <option value="not_yet" {{ old('la_math_thinking', $data['la_math_thinking'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                    @foreach($mathOptions as $c)
                        @php $label = $c === 'math_exempt' ? 'Math Placement Exempt' : $c; @endphp
                        <option value="{{ $c }}" {{ old('la_math_thinking', $data['la_math_thinking'] ?? '') === $c ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-actions">
                <a href="{{ $degree === 'bs_accounting' ? route('onboarding.step', 1) : route('onboarding.step', 2) }}" class="btn-secondary">← Back</a>
                <button type="submit" class="btn-primary">Next: Business Core →</button>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var filters = [
                { input: 'filter-natural-science',  select: 'select-natural-science' },
                { input: 'filter-literature',        select: 'select-literature' },
                { input: 'filter-fine-arts',         select: 'select-fine-arts' },
                { input: 'filter-social-science',    select: 'select-social-science' },
                { input: 'filter-history-politics',  select: 'select-history-politics' },
            ];

            filters.forEach(function(pair) {
                var input  = document.getElementById(pair.input);
                var select = document.getElementById(pair.select);
                if (!input || !select) { return; }
                var allOptions = Array.from(select.options);
                input.addEventListener('input', function() {
                    var query = this.value.toLowerCase().trim();
                    select.innerHTML = '';
                    allOptions.forEach(function(option) {
                        if (option.value === '' || option.value === 'not_yet' || option.text.toLowerCase().includes(query)) {
                            select.appendChild(option.cloneNode(true));
                        }
                    });
                });
            });

            function initElecovalidator(inputId, errorId, validator) {
                var input   = document.getElementById(inputId);
                var errorEl = document.getElementById(errorId);
                if (!input || !errorEl) { return; }
                input.addEventListener('input', function () {
                    var val = this.value.trim().toUpperCase();
                    if (val === '') {
                        errorEl.textContent = '';
                        this.classList.remove('input-error', 'input-valid');
                    } else if (validator(val)) {
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

            initElecovalidator('la_phil_elective', 'err_la_phil_elective', function(val) {
                return val.indexOf('PHIL ') === 0 || val === 'HSPH 203' || val === 'HSPH 204';
            });

            initElecovalidator('la_theology_elective', 'err_la_theology_elective', function(val) {
                return val.indexOf('TRS ') === 0 || val.indexOf('HSTR ') === 0 || val === 'HSEV 102';
            });
        });
    </script>

</body>
</html>
