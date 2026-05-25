<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Profile — Busch School</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --cua-blue:      #0a3255;
            --cua-blue-mid:  #1a4a6e;
            --cua-red:       #B41100;
            --cua-red-dark:  #8C0D00;
            --cua-gold:      #C9A84C;
            --sandstone:     #f7f3ed;
            --limestone:     #efebe9;
            --border:        #e2ddd8;
            --border-light:  #ede9e4;
            --text-muted:    #6b7280;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--limestone);
            color: #1a1a1a;
            min-height: 100vh;
        }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.03em;
        }
        .badge-completed   { background: #d1fae5; color: #065f46; }
        .badge-in-progress { background: #fef3c7; color: #92400e; }
        .badge-not-yet     { background: #f3f4f6; color: #9ca3af; }
        .badge-transfer    { background: #dbeafe; color: #1e40af; }
        .badge-standing    { background: rgba(10,50,85,0.1); color: var(--cua-blue); }
        .badge-spec        { background: rgba(201,168,76,0.18); color: #6b4d00; border: 1px solid rgba(201,168,76,0.4); }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .summary-card-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.4rem;
        }

        .summary-card-count {
            font-family: 'Oswald', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--cua-blue);
            line-height: 1.1;
        }

        .summary-card-count span {
            font-size: 15px;
            font-weight: 400;
            color: var(--text-muted);
        }

        .progress-track {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--cua-red);
            border-radius: 3px;
            transition: width 0.4s ease;
        }

        .progress-fill.complete { background: #10b981; }

        .section-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-light);
            background: var(--sandstone);
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--cua-blue);
        }

        .completion-label {
            font-size: 12.5px;
            color: var(--text-muted);
            font-weight: 500;
        }

        table { width: 100%; border-collapse: collapse; }
        th {
            font-family: 'Roboto', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 10px 20px;
            border-bottom: 1px solid var(--border-light);
            background: #faf8f6;
            text-align: left;
        }
        td {
            padding: 11px 20px;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid var(--border-light);
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #faf8f6; }

        .row-not-yet td { background: #fafafa; }
        .row-not-yet td:first-child { color: #c0b8b0; font-style: italic; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            padding: 20px;
        }

        .info-item { display: flex; flex-direction: column; gap: 3px; }
        .info-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: var(--text-muted);
        }
        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: #1a1a1a;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            background: var(--cua-red);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.12s;
        }
        .btn-primary:hover { background: var(--cua-red-dark); color: #fff; }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            background: transparent;
            color: var(--cua-blue);
            border: 1.5px solid var(--cua-blue);
            border-radius: 8px;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.12s, color 0.12s;
        }
        .btn-secondary:hover { background: var(--cua-blue); color: #fff; }

        .spec-elective-note {
            font-size: 12px;
            color: var(--text-muted);
            font-style: italic;
            padding: 8px 20px;
            border-top: 1px dashed var(--border-light);
            background: #faf9f7;
        }
    </style>
</head>
<body>

{{-- Header --}}
<header style="background:var(--cua-blue); box-shadow:0 2px 12px rgba(0,0,0,0.22);">
    <div class="flex items-center justify-between px-5 h-[62px]">
        <a href="{{ route('chat') }}" class="flex items-center gap-3.5 no-underline">
            <img src="/images/busch_logo_white.png" alt="The Busch School of Business" class="h-9 w-auto">
            <div class="hidden sm:block leading-none">
                <p class="font-oswald font-semibold uppercase tracking-wide text-[16px] text-white leading-none"
                   style="letter-spacing:0.04em;">Academic Profile</p>
                <p class="text-[10px] mt-1 tracking-wider uppercase font-light"
                   style="color:rgba(255,255,255,0.45);">Busch School of Business &middot; CUA</p>
            </div>
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('chat') }}" class="btn-secondary" style="border-color:rgba(255,255,255,0.6); color:#fff;"
               onmouseover="this.style.background='rgba(255,255,255,0.12)';"
               onmouseout="this.style.background='transparent';">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Back to Chat
            </a>
        </div>
    </div>
    <div class="h-[2px]" style="background:linear-gradient(to right, var(--cua-gold) 0%, rgba(201,168,76,0.2) 60%, transparent 100%);"></div>
</header>

{{-- Main content --}}
<div style="max-width:960px; margin:0 auto; padding:2rem 1.5rem 4rem;">

    @if(! $profile)
        <div class="section-card" style="text-align:center; padding:3rem 2rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.25" style="color:#d4cec8; margin:0 auto 1rem;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
            <h2 style="font-family:'Oswald',sans-serif; font-size:22px; font-weight:600; color:var(--cua-blue); margin-bottom:0.5rem;">No Academic Profile Yet</h2>
            <p style="color:var(--text-muted); font-size:15px; margin-bottom:1.5rem; max-width:420px; margin-left:auto; margin-right:auto;">
                Complete your academic profile setup to get personalized course planning assistance.
            </p>
            <a href="{{ route('onboarding') }}" class="btn-primary">Complete Onboarding</a>
        </div>
    @else

    @php
        $degreeLabel  = $profile->degree === 'bs_accounting' ? 'B.S. Accounting' : 'B.S.B.A.';
        $catalogLabel = $profile->catalog_year === 'post_2024' ? 'Post-2024 Catalog' : 'Pre-2024 Catalog';

        $statusBadge = function(string $status): string {
            return match($status) {
                'completed'   => '<span class="badge badge-completed">Completed</span>',
                'in_progress' => '<span class="badge badge-in-progress">In Progress</span>',
                'not_yet'     => '<span class="badge badge-not-yet">Not Yet</span>',
                default       => '<span class="badge badge-not-yet">Not Yet</span>',
            };
        };
    @endphp

        {{-- Page title --}}
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 style="font-family:'Oswald',sans-serif; font-size:26px; font-weight:700; color:var(--cua-blue); margin:0 0 4px;">
                    {{ $profile->full_name }}
                </h1>
                <p style="color:var(--text-muted); font-size:14px;">{{ $degreeLabel }} &middot; {{ $catalogLabel }}</p>
            </div>
            <a href="{{ route('chat') }}?msg=Please+review+and+update+my+academic+profile" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                </svg>
                Update via Bot
            </a>
        </div>

        {{-- Completion summary cards --}}
        @php
            $laPct   = $summaries['la']['total']   > 0 ? round($summaries['la']['completed']   / $summaries['la']['total']   * 100) : 0;
            $corePct = $summaries['core']['total']  > 0 ? round($summaries['core']['completed']  / $summaries['core']['total']  * 100) : 0;
        @endphp
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-label">Liberal Arts</div>
                <div class="summary-card-count">{{ $summaries['la']['completed'] }}<span> / {{ $summaries['la']['total'] }}</span></div>
                <div class="progress-track">
                    <div class="progress-fill {{ $laPct === 100 ? 'complete' : '' }}" style="width:{{ $laPct }}%"></div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Business Core</div>
                <div class="summary-card-count">{{ $summaries['core']['completed'] }}<span> / {{ $summaries['core']['total'] }}</span></div>
                <div class="progress-track">
                    <div class="progress-fill {{ $corePct === 100 ? 'complete' : '' }}" style="width:{{ $corePct }}%"></div>
                </div>
            </div>
            @foreach($specBlocks as $block)
            @php $specPct = $block['total_required'] > 0 ? round(min($block['completed'], $block['total_required']) / $block['total_required'] * 100) : 0; @endphp
            <div class="summary-card">
                <div class="summary-card-label">{{ $block['name'] }}</div>
                <div class="summary-card-count">{{ $block['completed'] }}<span> / {{ $block['total_required'] }}</span></div>
                <div class="progress-track">
                    <div class="progress-fill {{ $specPct === 100 ? 'complete' : '' }}" style="width:{{ $specPct }}%"></div>
                </div>
            </div>
            @endforeach
            @if($transferCourses->count() > 0)
            <div class="summary-card">
                <div class="summary-card-label">Transfer Credits</div>
                <div class="summary-card-count">{{ $transferCourses->count() }}<span> course{{ $transferCourses->count() !== 1 ? 's' : '' }}</span></div>
                <div class="progress-track"><div class="progress-fill complete" style="width:100%"></div></div>
            </div>
            @endif
        </div>

        {{-- Student Information --}}
        <div class="section-card">
            <div class="section-header">
                <span class="section-title">Student Information</span>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Degree</span>
                    <span class="info-value">{{ $degreeLabel }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Catalog Year</span>
                    <span class="info-value">{{ $catalogLabel }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Admit Term</span>
                    <span class="info-value">{{ $profile->admit_term ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Expected Graduation</span>
                    <span class="info-value">{{ $profile->expected_graduation ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">GPA</span>
                    <span class="info-value">{{ $profile->gpa ? number_format($profile->gpa, 2) : 'Not provided' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Credits Completed</span>
                    <span class="info-value">{{ $profile->credits_completed ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Academic Standing</span>
                    <span class="info-value">
                        @if($profile->projected_standing)
                            <span class="badge badge-standing">{{ ucfirst(str_replace('_', ' ', $profile->projected_standing)) }}</span>
                        @else
                            —
                        @endif
                    </span>
                </div>
            </div>
            @php
                $specs = array_filter([
                    $profile->specialization_1,
                    $profile->specialization_2,
                    $profile->specialization_3,
                ]);
            @endphp
            @if(count($specs) > 0)
            <div style="padding:0 20px 16px; border-top:1px solid var(--border-light);">
                <p class="info-label" style="padding-top:14px; margin-bottom:8px;">Specializations</p>
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach($specs as $spec)
                        <span class="badge badge-spec" style="font-size:13px; padding:4px 12px;">{{ ucwords(str_replace('_', ' ', $spec)) }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Liberal Arts --}}
        <div class="section-card">
            <div class="section-header">
                <span class="section-title">Liberal Arts Requirements</span>
                <span class="completion-label">{{ $summaries['la']['completed'] }} of {{ $summaries['la']['total'] }} completed</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:160px;">Course Code</th>
                        <th>Requirement</th>
                        <th style="width:140px;">Status</th>
                        <th style="width:130px;">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($laRows as $row)
                    <tr class="{{ $row['status'] === 'not_yet' ? 'row-not-yet' : '' }}">
                        <td style="font-family:monospace; font-size:13px; font-weight:600; color:{{ $row['status'] !== 'not_yet' ? 'var(--cua-blue)' : '#bbb' }};">
                            {{ $row['course_code'] }}
                        </td>
                        <td>{{ $row['slot_name'] }}</td>
                        <td>{!! $statusBadge($row['status']) !!}</td>
                        <td style="color:var(--text-muted); font-size:13px;">{{ $row['semester'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Business Core --}}
        <div class="section-card">
            <div class="section-header">
                <span class="section-title">Business Core</span>
                <span class="completion-label">{{ $summaries['core']['completed'] }} of {{ $summaries['core']['total'] }} completed</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:160px;">Course Code</th>
                        <th>Requirement</th>
                        <th style="width:140px;">Status</th>
                        <th style="width:130px;">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coreRows as $row)
                    <tr class="{{ $row['status'] === 'not_yet' ? 'row-not-yet' : '' }}">
                        <td style="font-family:monospace; font-size:13px; font-weight:600; color:{{ $row['status'] !== 'not_yet' ? 'var(--cua-blue)' : '#bbb' }};">
                            {{ $row['course_code'] }}
                        </td>
                        <td>{{ $row['slot_name'] }}</td>
                        <td>{!! $statusBadge($row['status']) !!}</td>
                        <td style="color:var(--text-muted); font-size:13px;">{{ $row['semester'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Specialization blocks --}}
        @foreach($specBlocks as $block)
        <div class="section-card">
            <div class="section-header">
                <span class="section-title">{{ $block['name'] }} Specialization</span>
                <span class="completion-label">{{ $block['completed'] }} of {{ $block['total_required'] }} required completed</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:160px;">Course Code</th>
                        <th>Type</th>
                        <th style="width:140px;">Status</th>
                        <th style="width:130px;">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($block['rows'] as $row)
                    <tr class="{{ $row['status'] === 'not_yet' ? 'row-not-yet' : '' }}">
                        <td style="font-family:monospace; font-size:13px; font-weight:600; color:{{ $row['status'] !== 'not_yet' ? 'var(--cua-blue)' : '#bbb' }};">
                            {{ $row['course_code'] }}
                        </td>
                        <td>
                            @if($row['type'] === 'Required')
                                <span style="font-size:12px; font-weight:600; color:var(--cua-blue);">Required</span>
                            @else
                                <span style="font-size:12px; color:var(--text-muted);">Elective</span>
                            @endif
                        </td>
                        <td>{!! $statusBadge($row['status']) !!}</td>
                        <td style="color:var(--text-muted); font-size:13px;">{{ $row['semester'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($block['choose_count'] > 0)
            <div class="spec-elective-note">
                Choose {{ $block['choose_count'] }} elective{{ $block['choose_count'] !== 1 ? 's' : '' }} from the list above in addition to required courses.
            </div>
            @endif
        </div>
        @endforeach

        {{-- Transfer Credits --}}
        @if($transferCourses->count() > 0)
        <div class="section-card">
            <div class="section-header">
                <span class="section-title">Transfer Credits</span>
                <span class="completion-label">{{ $transferCourses->count() }} course{{ $transferCourses->count() !== 1 ? 's' : '' }}</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:160px;">CUA Equivalent</th>
                        <th>Original Course</th>
                        <th style="width:100px;">Grade</th>
                        <th>Institution / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transferCourses as $course)
                    <tr>
                        <td style="font-family:monospace; font-size:13px; font-weight:600; color:var(--cua-blue);">
                            {{ $course->course_code }}
                        </td>
                        <td>{{ $course->course_name }}</td>
                        <td>
                            @if($course->grade)
                                <span class="badge badge-completed">{{ $course->grade }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="color:var(--text-muted); font-size:13px;">{{ $course->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Other / bot-updated courses --}}
        @if($otherCourses->count() > 0)
        <div class="section-card">
            <div class="section-header">
                <span class="section-title">Additional Courses</span>
                <span class="completion-label">{{ $otherCourses->count() }} course{{ $otherCourses->count() !== 1 ? 's' : '' }}</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:160px;">Course Code</th>
                        <th>Course Name</th>
                        <th style="width:140px;">Status</th>
                        <th style="width:130px;">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($otherCourses as $course)
                    <tr>
                        <td style="font-family:monospace; font-size:13px; font-weight:600; color:var(--cua-blue);">
                            {{ $course->course_code ?? '—' }}
                        </td>
                        <td>{{ $course->course_name ?? '—' }}</td>
                        <td>{!! $statusBadge($course->status ?? 'not_yet') !!}</td>
                        <td style="color:var(--text-muted); font-size:13px;">{{ $course->semester_completed ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <p style="text-align:center; font-size:12px; color:#a89f97; margin-top:2rem; line-height:1.6;">
            Profile updates are made through the academic planning bot.
            <a href="{{ route('chat') }}?msg=Please+review+and+update+my+academic+profile"
               style="color:var(--cua-blue); text-decoration:underline;">Go to the chat</a>
            to ask your advisor to update your profile.
        </p>

    @endif
</div>

</body>
</html>
