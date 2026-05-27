<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Profile Setup — Step 6 | Busch School</title>

    <link rel="stylesheet" href="/fonts/fonts.css">

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

        .field-hint {
            font-size: 0.8rem;
            color: #888;
            margin-top: 0.25rem;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            font-family: 'Roboto', sans-serif;
            transition: border-color 0.2s;
            background: #fff;
        }

        input:focus {
            outline: none;
            border-color: var(--cua-navy);
            box-shadow: 0 0 0 2px rgba(0,51,102,0.12);
        }

        .standing-badge {
            display: inline-block;
            background: var(--cua-navy);
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.95rem;
            margin-top: 0.5rem;
        }

        .standing-row {
            margin-top: 0.5rem;
            font-size: 0.92rem;
            color: #555;
        }

        .course-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eef1f7;
            border: 1px solid #cdd4e8;
            color: var(--cua-navy);
            font-size: 0.88rem;
            font-weight: 500;
            padding: 3px 10px 3px 10px;
            border-radius: 20px;
            margin: 3px 3px 3px 0;
        }

        .course-tag button {
            background: none;
            border: none;
            cursor: pointer;
            color: #aaa;
            font-size: 15px;
            line-height: 1;
            padding: 0;
            margin-left: 2px;
            transition: color 0.12s;
        }

        .course-tag button:hover { color: var(--cua-red); }

        .course-tags-container {
            display: flex;
            flex-wrap: wrap;
            min-height: 40px;
            padding: 0.4rem;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            background: #fff;
            transition: border-color 0.2s;
            cursor: text;
        }

        .course-tags-container:focus-within {
            border-color: var(--cua-navy);
            box-shadow: 0 0 0 2px rgba(0,51,102,0.12);
        }

        .add-course-row {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.6rem;
        }

        .add-course-row input[type="text"] {
            flex: 1;
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
        }

        .btn-add {
            background: var(--cua-navy);
            color: white;
            border: none;
            padding: 0.5rem 1.1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.15s;
            flex-shrink: 0;
        }

        .btn-add:hover { background: var(--cua-dark); }

        .btn-primary {
            background: var(--cua-red);
            color: white;
            border: none;
            padding: 0.85rem 2.5rem;
            font-size: 1.05rem;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            letter-spacing: 0.05em;
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
            margin: 0.6rem 0;
            font-size: 0.9rem;
        }

        .alert-info {
            background: #e8f0fe;
            border-left: 4px solid var(--cua-navy);
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin: 0.6rem 0;
            font-size: 0.9rem;
            color: var(--cua-dark);
        }

        .alert-success {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin: 0 0 1.5rem;
            font-size: 0.9rem;
            color: #166534;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 480px) {
            .two-col { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    @include('onboarding.partials.header', ['step' => $step, 'totalSteps' => $totalSteps])

    @php
        $bus199   = $data['core_bus199']   ?? 'not_yet';
        $bus299a  = $data['core_bus299a']  ?? 'not_yet';
        $bus399a  = $data['core_bus399a']  ?? 'not_yet';
        $preloadCourses = $data['in_progress_courses'] ?? [];
        $preloadJson    = json_encode(array_values(array_filter($preloadCourses)));
    @endphp

    <div class="wizard-card"
         x-data="{
            credits: {{ old('credits_completed', $data['credits_completed'] ?? 0) }},
            courses: {{ $preloadJson }},
            newCourse: '',
            bus199Status: '{{ $bus199 }}',
            bus299aStatus: '{{ $bus299a }}',
            bus399aStatus: '{{ $bus399a }}',
            get standing() {
                const c = parseInt(this.credits) || 0;
                if (c >= 90) return 'Senior';
                if (c >= 60) return 'Junior';
                if (c >= 30) return 'Sophomore';
                return 'Freshman';
            },
            get showBus199Warning() {
                return parseInt(this.credits) >= 30 && (this.bus199Status === 'not_yet' || this.bus199Status === '');
            },
            get showBus299aWarning() {
                return parseInt(this.credits) >= 60 && (this.bus299aStatus === 'not_yet' || this.bus299aStatus === '');
            },
            get showBus399aWarning() {
                return parseInt(this.credits) >= 90 && (this.bus399aStatus === 'not_yet' || this.bus399aStatus === '');
            },
            addCourse() {
                const code = this.newCourse.trim().toUpperCase();
                if (code && !this.courses.includes(code)) {
                    this.courses.push(code);
                }
                this.newCourse = '';
            },
            removeCourse(code) {
                this.courses = this.courses.filter(c => c !== code);
            }
         }"
    >

        <div class="alert-success">
            Almost done! Review your information and click <strong>Complete Setup</strong> to access your personalized course planning advisor.
        </div>

        @if ($errors->any())
            <div class="alert-warning">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('onboarding.save', 6) }}">
            @csrf
            <x-honeypot />

            {{-- Credits & GPA --}}
            <h3 class="step-heading">Academic Standing</h3>

            <div class="two-col">
                <div class="form-group">
                    <label class="field-label" for="credits_completed">Credits Completed</label>
                    <input
                        type="number"
                        id="credits_completed"
                        name="credits_completed"
                        x-model="credits"
                        min="0"
                        max="200"
                        value="{{ old('credits_completed', $data['credits_completed'] ?? 0) }}"
                        placeholder="e.g. 60"
                    >
                    <div class="standing-row" x-show="credits > 0 || credits === 0">
                        You are a: <span class="standing-badge" x-text="standing"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="field-label" for="gpa">Cumulative GPA</label>
                    <input
                        type="number"
                        id="gpa"
                        name="gpa"
                        min="0"
                        max="4.00"
                        step="0.01"
                        value="{{ old('gpa', $data['gpa'] ?? '') }}"
                        placeholder="e.g. 3.25"
                    >
                    <p class="field-hint">Enter your cumulative GPA (0.00 – 4.00)</p>
                </div>
            </div>

            {{-- Career Discernment Warnings --}}
            <div x-show="showBus199Warning" x-transition class="alert-warning">
                You have 30+ credits but BUS 199 (Career Discernment I) is not yet completed.
            </div>
            <div x-show="showBus299aWarning" x-transition class="alert-warning">
                You have 60+ credits but BUS 299A (Career Discernment II) is not yet completed.
            </div>
            <div x-show="showBus399aWarning" x-transition class="alert-warning">
                You have 90+ credits but BUS 399A (Career Discernment III) is not yet completed.
            </div>

            {{-- In-Progress Courses --}}
            <h3 class="step-heading">Courses Currently In Progress</h3>
            <p style="font-size:0.88rem; color:#666; margin: 0 0 0.75rem;">
                Add the course codes for classes you are currently enrolled in this semester.
            </p>

            {{-- Tags display --}}
            <div class="course-tags-container" @click="$refs.newCourseInput.focus()">
                <template x-for="course in courses" :key="course">
                    <span class="course-tag">
                        <span x-text="course"></span>
                        <button type="button" @click.stop="removeCourse(course)" :aria-label="'Remove ' + course">&times;</button>
                        <input type="hidden" name="in_progress_courses[]" :value="course">
                    </span>
                </template>
            </div>

            {{-- Add input --}}
            <div class="add-course-row">
                <input
                    type="text"
                    x-ref="newCourseInput"
                    x-model="newCourse"
                    @keydown.enter.prevent="addCourse()"
                    placeholder="e.g. MGT 311"
                    autocomplete="off"
                    autocapitalize="characters"
                >
                <button type="button" class="btn-add" @click="addCourse()">Add</button>
            </div>
            <p style="font-size:0.78rem; color:#aaa; margin: 0.3rem 0 0;">
                Press Enter or click Add. These courses will be factored into your advising.
            </p>

            <div class="form-actions">
                <a href="{{ $degree === 'bs_accounting' ? route('onboarding.step.accounting') : route('onboarding.step', 5) }}" class="btn-secondary">← Back</a>
                <button type="submit" class="btn-primary">Complete Setup →</button>
            </div>

        </form>
    </div>

</body>
</html>
