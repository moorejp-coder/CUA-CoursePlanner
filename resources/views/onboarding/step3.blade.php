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

        .form-group { margin-bottom: 1rem; }

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
    </style>
</head>
<body>

    @include('onboarding.partials.header', ['step' => $step, 'totalSteps' => $totalSteps])

    <div class="wizard-card">

        <div class="alert-info">
            Select the courses you have <strong>already completed</strong>. Choose "Not yet completed" for requirements still in progress.
        </div>

        @if ($errors->any())
            <div class="alert-warning">{{ $errors->first() }}</div>
        @endif

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
                        <option value="ENG 101" {{ old('la_rhetoric', $data['la_rhetoric'] ?? '') === 'ENG 101' ? 'selected' : '' }}>ENG 101</option>
                    </select>
                </div>

                {{-- Natural Science --}}
                <div class="form-group">
                    <label class="field-label" for="la_natural_science">Natural Science</label>
                    <select id="la_natural_science" name="la_natural_science">
                        <option value="not_yet" {{ old('la_natural_science', $data['la_natural_science'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['ANTH 105','ANTH 108','ANTH 204','ANTH 206','BIOL 103','BIOL 109','CHEM 101','CHEM 110','CHEM 125','PHYS 101','PHYS 103','PHYS 122','PHYS 206','PSY 204','SAS 225'] as $c)
                            <option value="{{ $c }}" {{ old('la_natural_science', $data['la_natural_science'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Literature --}}
                <div class="form-group">
                    <label class="field-label" for="la_literature">Literature</label>
                    <select id="la_literature" name="la_literature">
                        <option value="not_yet" {{ old('la_literature', $data['la_literature'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['ENG 212','ENG 231','ENG 232','ENG 235','ENG 236','ENG 278','ENG 300+','ENG 400+','FREN Lit','GER Lit','ITAL Lit','SPAN Lit','CLAS/HUM'] as $c)
                            <option value="{{ $c }}" {{ old('la_literature', $data['la_literature'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Fine Arts --}}
                <div class="form-group">
                    <label class="field-label" for="la_fine_arts">Fine Arts</label>
                    <select id="la_fine_arts" name="la_fine_arts">
                        <option value="not_yet" {{ old('la_fine_arts', $data['la_fine_arts'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['ART 201','ART 210','ART 215','ART 300+','DR 105','DR 106','DR 110','DR 201','DR 202','DR 207','DR 305','DR 403','MUS 110','MUS 112','MUS 131','MUS 134','MUS 135','MUS 178','MUS 276','MUS 304','MDIA 201','MDIA 343','DNCE 101'] as $c)
                            <option value="{{ $c }}" {{ old('la_fine_arts', $data['la_fine_arts'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- History/Politics --}}
                <div class="form-group">
                    <label class="field-label" for="la_history_politics">History / Politics</label>
                    <select id="la_history_politics" name="la_history_politics">
                        <option value="not_yet" {{ old('la_history_politics', $data['la_history_politics'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['POL 111','POL 112','POL 211','POL 226','HIST 140','HIST 200+','HIST 300+','CLAS 205','CLAS 308','WASH 101','EURO 203'] as $c)
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

            </div>{{-- end .form-grid --}}

            {{-- Additional Requirements --}}
            <h3 class="step-heading">Additional Requirements</h3>

            <div class="form-grid">

                {{-- Philosophy Elective --}}
                <div class="form-group">
                    <label class="field-label" for="la_phil_elective">Philosophy Elective</label>
                    <select id="la_phil_elective" name="la_phil_elective">
                        <option value="not_yet" {{ old('la_phil_elective', $data['la_phil_elective'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['PHIL 300+','PHIL 301','PHIL 320','PHIL 340','PHIL 350','PHIL 400+'] as $c)
                            <option value="{{ $c }}" {{ old('la_phil_elective', $data['la_phil_elective'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Theology Elective --}}
                <div class="form-group">
                    <label class="field-label" for="la_theology_elective">Theology Elective</label>
                    <select id="la_theology_elective" name="la_theology_elective">
                        <option value="not_yet" {{ old('la_theology_elective', $data['la_theology_elective'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                        @foreach(['TRS 300+','TRS 301','TRS 325','TRS 350','TRS 400+'] as $c)
                            <option value="{{ $c }}" {{ old('la_theology_elective', $data['la_theology_elective'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <a href="{{ route('onboarding.step', 2) }}" class="btn-secondary">← Back</a>
                <button type="submit" class="btn-primary">Next: Business Core →</button>
            </div>

        </form>
    </div>

</body>
</html>
