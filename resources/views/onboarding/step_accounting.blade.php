<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Profile Setup — Accounting Requirements | Busch School</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --cua-red:  #B41100;
            --cua-navy: #003366;
            --cua-gold: #C9A84C;
            --cua-dark: #0a3255;
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

        .wizard-header { text-align: center; margin-bottom: 0.5rem; width: 100%; max-width: 720px; }
        .wizard-logo { display: block; margin: 0 auto 1rem; height: 60px; object-fit: contain; }
        .wizard-title { font-family: 'Oswald', sans-serif; font-size: 1.8rem; font-weight: 700; color: var(--cua-navy); text-align: center; margin: 0 0 0.25rem; }
        .wizard-subtitle { text-align: center; color: #666; font-size: 0.9rem; margin: 0 0 1rem; }

        .progress-bar-container { width: 100%; max-width: 720px; height: 8px; background: #e0e0e0; border-radius: 4px; margin-bottom: 0.75rem; overflow: hidden; }
        .progress-bar-fill { height: 100%; background: var(--cua-red); border-radius: 4px; transition: width 0.4s ease; }

        .step-labels { display: flex; justify-content: space-between; width: 100%; max-width: 720px; margin-bottom: 1.5rem; }
        .step-label { font-size: 0.7rem; color: #aaa; font-weight: 500; text-align: center; flex: 1; }
        .step-label.active { color: var(--cua-red); font-weight: 700; }
        .step-label.done   { color: var(--cua-navy); }

        .wizard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 2.5rem;
            width: 100%;
            max-width: 720px;
            margin-bottom: 2rem;
        }

        .step-heading {
            font-family: 'Oswald', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--cua-navy);
            margin: 1.5rem 0 0.75rem;
            padding-bottom: 0.4rem;
            border-bottom: 2px solid var(--cua-gold);
        }

        .step-heading:first-of-type { margin-top: 0; }

        .alert-info {
            background: #e8f0fe;
            border-left: 4px solid var(--cua-navy);
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--cua-dark);
        }

        .alert-warning {
            background: #fffbea;
            border-left: 4px solid var(--cua-gold);
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin-bottom: 0.75rem;
            font-size: 0.88rem;
            color: #5c3d00;
        }

        .course-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .course-table th {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #6b7280;
            padding: 0.5rem 0.75rem;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
            background: #faf8f6;
        }

        .course-table td {
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid #f0ede9;
            vertical-align: middle;
        }

        .course-table tr:last-child td { border-bottom: none; }

        .course-code {
            font-family: monospace;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--cua-navy);
            white-space: nowrap;
        }

        .course-name { font-size: 0.9rem; color: #374151; }

        .prereq-note {
            font-size: 0.75rem;
            color: #9ca3af;
            font-style: italic;
        }

        select.status-select {
            padding: 0.4rem 0.65rem;
            border: 1.5px solid #ccc;
            border-radius: 5px;
            font-size: 0.85rem;
            font-family: 'Roboto', sans-serif;
            background: #fff;
            cursor: pointer;
            min-width: 130px;
        }

        select.status-select:focus {
            outline: none;
            border-color: var(--cua-navy);
            box-shadow: 0 0 0 2px rgba(0,51,102,0.12);
        }

        .elective-group {
            background: #f8f7f4;
            border: 1px solid #e5e0d8;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-top: 0.5rem;
        }

        .elective-label {
            font-size: 0.88rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        select.elective-select {
            width: 100%;
            max-width: 340px;
            padding: 0.5rem 0.75rem;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            font-size: 0.9rem;
            font-family: 'Roboto', sans-serif;
            background: #fff;
            cursor: pointer;
        }

        select.elective-select:focus {
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
    </style>
</head>
<body>

    @include('onboarding.partials.header', ['step' => 'accounting'])

    @php
        $catalogYear       = $data['catalog_year'] ?? 'post_2024';
        $isPost2024        = $catalogYear === 'post_2024';
        $v                 = fn (string $field) => $data[$field] ?? 'not_yet';
        $ac                = fn (string $code) => $data['acct_courses'][$code] ?? 'not_yet';
        $requiredCourses   = $requirements[$catalogYear]['accounting_courses'] ?? [];
        $accountingElectives = $requirements[$catalogYear]['accounting_electives'] ?? [];
    @endphp

    <div class="wizard-card">

        <div class="alert-info">
            Select the status for each Accounting requirement. These are specific to the B.S. Accounting degree.
        </div>

        <div class="alert-warning">
            <strong>Note:</strong> BS Accounting requires <strong>MATH 111</strong>. Make sure this is reflected in your Business Core (step 3).
        </div>

        <div class="alert-warning">
            <strong>Note:</strong> BS Accounting students who wish to add a BSBA specialization must double-declare both programs.
        </div>

        <form method="POST" action="{{ route('onboarding.save.accounting') }}">
            @csrf
            <x-honeypot />

            <h3 class="step-heading">{{ $isPost2024 ? 'Post-2024' : 'Pre-2024' }} BS Accounting Requirements</h3>

            <table class="course-table">
                <thead>
                    <tr>
                        <th style="width:110px;">Code</th>
                        <th>Course</th>
                        <th style="width:160px;">Prerequisite</th>
                        <th style="width:150px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requiredCourses as $c)
                    <tr>
                        <td class="course-code">{{ $c['code'] }}</td>
                        <td class="course-name">{{ $c['name'] }}</td>
                        <td class="prereq-note">{{ $c['pre'] ?? '—' }}</td>
                        <td>
                            <select name="acct_courses[{{ $c['code'] }}]" class="status-select">
                                <option value="not_yet"     {{ $ac($c['code']) === 'not_yet'     ? 'selected' : '' }}>Not yet</option>
                                <option value="in_progress" {{ $ac($c['code']) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed"   {{ $ac($c['code']) === 'completed'   ? 'selected' : '' }}>Completed</option>
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if(!empty($accountingElectives))
            <h3 class="step-heading">Accounting Elective (choose one)</h3>
            <div class="elective-group">
                <p class="elective-label">Select the elective you have completed (or plan to complete):</p>
                <select name="acct_elective" class="elective-select">
                    <option value="not_yet" {{ ($data['acct_elective'] ?? 'not_yet') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                    @foreach($accountingElectives as $elective)
                        <option value="{{ $elective }}" {{ ($data['acct_elective'] ?? '') === $elective ? 'selected' : '' }}>{{ $elective }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="acct_elective" value="not_yet">
            @endif

            <div class="form-actions">
                <a href="{{ route('onboarding.step', 4) }}" class="btn-secondary">← Back</a>
                <button type="submit" class="btn-primary">Next: Current Status →</button>
            </div>

        </form>
    </div>

</body>
</html>
