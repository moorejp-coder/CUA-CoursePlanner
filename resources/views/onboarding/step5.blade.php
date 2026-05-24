<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Profile Setup — Step 5 | Busch School</title>

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

        .spec-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .spec-section:last-of-type { border-bottom: none; }

        .spec-section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--cua-navy);
            margin: 0 0 0.4rem;
        }

        .sub-heading {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #888;
            margin: 1rem 0 0.5rem;
        }

        .course-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.45rem 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .course-row:last-child { border-bottom: none; }

        .course-code {
            font-weight: 600;
            font-size: 0.92rem;
            color: #1a1a1a;
            min-width: 90px;
            flex-shrink: 0;
        }

        .course-row select {
            width: 200px;
            flex-shrink: 0;
            padding: 0.4rem 0.7rem;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            font-size: 0.88rem;
            font-family: 'Roboto', sans-serif;
            background: #fff;
            transition: border-color 0.2s;
        }

        .course-row select:focus {
            outline: none;
            border-color: var(--cua-navy);
            box-shadow: 0 0 0 2px rgba(0,51,102,0.12);
        }

        .elective-group-label {
            font-size: 0.82rem;
            color: #666;
            font-style: italic;
            margin: 0.5rem 0 0.35rem;
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
            padding: 0.65rem 0.9rem;
            border-radius: 4px;
            margin: 0.5rem 0;
            font-size: 0.86rem;
        }

        .alert-info {
            background: #e8f0fe;
            border-left: 4px solid var(--cua-navy);
            padding: 0.65rem 0.9rem;
            border-radius: 4px;
            margin: 0.5rem 0;
            font-size: 0.86rem;
            color: var(--cua-dark);
        }

        .empty-state {
            text-align: center;
            color: #999;
            padding: 2rem;
            font-style: italic;
        }
    </style>
</head>
<body>

    @include('onboarding.partials.header', ['step' => $step, 'totalSteps' => $totalSteps])

    @php
        $catalogYear = $data['catalog_year'] ?? 'post_2024';
        $allSpecs    = $specializations[$catalogYear]['specializations'] ?? [];

        $selectedSpecs = array_values(array_filter([
            $data['specialization_1'] ?? null,
            $data['specialization_2'] ?? null,
            $data['specialization_3'] ?? null,
        ]));

        /**
         * Helper: get the stored key for a course code.
         * Keys are stored as "spec_course_{courseCode}" where courseCode retains
         * spaces but non-alphanumeric-space chars are stripped.
         */
        $specCourseVal = fn(string $courseCode) =>
            old("spec_courses[{$courseCode}]", $data["spec_course_{$courseCode}"] ?? 'not_yet');
    @endphp

    <div class="wizard-card">

        @if ($errors->any())
            <div class="alert-warning">{{ $errors->first() }}</div>
        @endif

        @if (empty($selectedSpecs))
            <div class="empty-state">
                No specializations selected. <a href="{{ route('onboarding.step', 2) }}">Go back to Step 2</a> to choose your specialization(s).
            </div>
        @endif

        <form method="POST" action="{{ route('onboarding.save', 5) }}">
            @csrf

            @foreach ($selectedSpecs as $specKey)
                @php
                    $spec = $allSpecs[$specKey] ?? null;
                @endphp

                @if (!$spec)
                    @continue
                @endif

                <div class="spec-section">
                    <h3 class="spec-section-title">{{ $spec['name'] }}</h3>

                    @if (!empty($spec['must_double_specialize']))
                        <div class="alert-warning">This specialization requires a second specialization.</div>
                    @endif

                    @if (!empty($spec['prerequisites']))
                        <div class="alert-info">Note: {{ implode(', ', (array) $spec['prerequisites']) }} required for this specialization.</div>
                    @endif

                    @if (!empty($spec['notes']))
                        <div class="alert-info">{{ $spec['notes'] }}</div>
                    @endif

                    {{-- Required Courses --}}
                    @if (!empty($spec['required']))
                        <p class="sub-heading">Required Courses</p>
                        @foreach ($spec['required'] as $courseCode)
                            <div class="course-row">
                                <span class="course-code">{{ $courseCode }}</span>
                                <select name="spec_courses[{{ $courseCode }}]">
                                    <option value="not_yet"     {{ $specCourseVal($courseCode) === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                                    <option value="in_progress" {{ $specCourseVal($courseCode) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed"   {{ $specCourseVal($courseCode) === 'completed'   ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                        @endforeach
                    @endif

                    {{-- Elective Groups (international_business pre_2024 pattern) --}}
                    @if (!empty($spec['elective_groups']))
                        @foreach ($spec['elective_groups'] as $group)
                            <p class="sub-heading">Electives</p>
                            <p class="elective-group-label">{{ $group['description'] }} (choose {{ $group['choose_count'] }})</p>
                            @foreach ($group['courses'] as $courseCode)
                                <div class="course-row">
                                    <span class="course-code">{{ $courseCode }}</span>
                                    <select name="spec_courses[{{ $courseCode }}]">
                                        <option value="not_yet"     {{ $specCourseVal($courseCode) === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                                        <option value="in_progress" {{ $specCourseVal($courseCode) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed"   {{ $specCourseVal($courseCode) === 'completed'   ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                            @endforeach
                        @endforeach
                    @elseif (!empty($spec['electives']) && $spec['choose_count'] > 0)
                        {{-- Standard electives --}}
                        <p class="sub-heading">Electives (choose {{ $spec['choose_count'] }})</p>
                        @foreach ($spec['electives'] as $courseCode)
                            <div class="course-row">
                                <span class="course-code">{{ $courseCode }}</span>
                                <select name="spec_courses[{{ $courseCode }}]">
                                    <option value="not_yet"     {{ $specCourseVal($courseCode) === 'not_yet'     ? 'selected' : '' }}>Not yet completed</option>
                                    <option value="in_progress" {{ $specCourseVal($courseCode) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed"   {{ $specCourseVal($courseCode) === 'completed'   ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                        @endforeach
                    @endif

                </div>
            @endforeach

            <div class="form-actions">
                <a href="{{ route('onboarding.step', 4) }}" class="btn-secondary">← Back</a>
                <button type="submit" class="btn-primary">Next: Current Status →</button>
            </div>

        </form>
    </div>

</body>
</html>
