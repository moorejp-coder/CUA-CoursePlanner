<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Profile Setup — Step 1 | Busch School</title>

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

        .form-group { margin-bottom: 1.25rem; }

        label.field-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            font-family: 'Roboto', sans-serif;
            transition: border-color 0.2s;
            background: #fff;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--cua-navy);
            box-shadow: 0 0 0 2px rgba(0,51,102,0.12);
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.65rem 1rem;
            border: 1.5px solid #e0e0e0;
            border-radius: 6px;
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
        }

        .radio-option:hover { border-color: var(--cua-navy); background: #f8faff; }
        .radio-option input[type="radio"] { accent-color: var(--cua-red); width: 1rem; height: 1rem; flex-shrink: 0; }
        .radio-option span { font-size: 0.95rem; line-height: 1.4; }

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
    </style>
</head>
<body>

    @include('onboarding.partials.header', ['step' => $step, 'totalSteps' => $totalSteps])

    <div class="wizard-card">

        @if ($errors->any())
            <div class="alert-warning">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('onboarding.save', 1) }}">
            @csrf

            <h3 class="step-heading">Basic Information</h3>

            {{-- Full Name --}}
            <div class="form-group">
                <label class="field-label" for="full_name">Full Name</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="{{ old('full_name', $data['full_name'] ?? '') }}"
                    placeholder="e.g. Jane Smith"
                    required
                >
            </div>

            {{-- Admit Term --}}
            <div class="form-group" x-data="{
                    admitTerm: '{{ old('admit_term', $data['admit_term'] ?? '') }}',
                    get catalogYear() {
                        const post2024 = ['Spring 2024','Fall 2024','Spring 2025','Fall 2025','Spring 2026'];
                        if (!this.admitTerm) return null;
                        return post2024.includes(this.admitTerm) ? 'post_2024' : 'pre_2024';
                    }
                }">
                <label class="field-label" for="admit_term">Admit Term</label>
                <select id="admit_term" name="admit_term" x-model="admitTerm" required>
                    <option value="">— Select your admit term —</option>
                    @foreach(['Fall 2020','Spring 2021','Fall 2021','Spring 2022','Fall 2022','Spring 2023','Fall 2023','Spring 2024','Fall 2024','Spring 2025','Fall 2025','Spring 2026'] as $term)
                        <option value="{{ $term }}" {{ old('admit_term', $data['admit_term'] ?? '') === $term ? 'selected' : '' }}>{{ $term }}</option>
                    @endforeach
                </select>

                {{-- Catalog year notice --}}
                <div x-show="catalogYear !== null" x-transition class="alert-info" style="margin-top:0.6rem;">
                    Based on your admit term, you are on the
                    <strong x-text="catalogYear === 'post_2024' ? 'Post-2024' : 'Pre-2024'"></strong> catalog.
                </div>

                {{-- Hidden catalog_year field --}}
                <input type="hidden" name="catalog_year" :value="catalogYear ?? ''">
            </div>

            {{-- Degree Program --}}
            <div class="form-group">
                <label class="field-label">Degree Program</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input
                            type="radio"
                            name="degree"
                            value="bsba"
                            {{ old('degree', $data['degree'] ?? 'bsba') === 'bsba' ? 'checked' : '' }}
                        >
                        <span><strong>B.S.B.A.</strong> — Bachelor of Science in Business Administration</span>
                    </label>
                    <label class="radio-option">
                        <input
                            type="radio"
                            name="degree"
                            value="bs_accounting"
                            {{ old('degree', $data['degree'] ?? '') === 'bs_accounting' ? 'checked' : '' }}
                        >
                        <span><strong>B.S. Accounting</strong> — Bachelor of Science in Accounting</span>
                    </label>
                </div>
            </div>

            {{-- Expected Graduation --}}
            <div class="form-group">
                <label class="field-label" for="expected_graduation">Expected Graduation</label>
                <select id="expected_graduation" name="expected_graduation" required>
                    <option value="">— Select expected graduation —</option>
                    @foreach(['Spring 2025','Fall 2025','Spring 2026','Fall 2026','Spring 2027','Fall 2027','Spring 2028','Fall 2028','Spring 2029','Fall 2029','Spring 2030'] as $term)
                        <option value="{{ $term }}" {{ old('expected_graduation', $data['expected_graduation'] ?? '') === $term ? 'selected' : '' }}>{{ $term }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-actions">
                <span></span>
                <button type="submit" class="btn-primary">Next: Specializations →</button>
            </div>

        </form>
    </div>

</body>
</html>
