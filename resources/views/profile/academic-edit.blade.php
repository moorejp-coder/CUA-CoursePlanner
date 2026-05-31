<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Academic Profile — Busch School</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --cua-blue:     #0a3255;
            --cua-red:      #B41100;
            --cua-red-dark: #8C0D00;
            --cua-gold:     #C9A84C;
            --sandstone:    #f7f3ed;
            --limestone:    #efebe9;
            --border:       #e2ddd8;
            --border-light: #ede9e4;
            --text-muted:   #6b7280;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: var(--limestone);
            color: #1a1a1a;
            min-height: 100vh;
        }
        [x-cloak] { display: none !important; }

        /* ── Form elements ── */
        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .field-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: 'Roboto', sans-serif;
            font-size: 14.5px;
            color: #1a1a1a;
            background: #fff;
            transition: border-color 0.15s, box-shadow 0.15s;
            appearance: none;
        }
        .field-input:focus {
            outline: none;
            border-color: var(--cua-blue);
            box-shadow: 0 0 0 3px rgba(10,50,85,0.1);
        }
        .field-input.error { border-color: #ef4444; }
        select.field-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 38px;
        }
        .field-error {
            margin-top: 5px;
            font-size: 12.5px;
            color: #dc2626;
        }
        .radio-group { display: flex; gap: 12px; flex-wrap: wrap; }
        .radio-card {
            flex: 1;
            min-width: 140px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
            background: #fff;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .radio-card:has(input:checked) {
            border-color: var(--cua-blue);
            background: rgba(10,50,85,0.04);
        }
        .radio-card input[type="radio"] { margin-top: 2px; flex-shrink: 0; accent-color: var(--cua-blue); }
        .radio-card-title { font-size: 14px; font-weight: 600; color: #1a1a1a; }
        .radio-card-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        /* ── Section cards ── */
        .section-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .section-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-light);
            background: var(--sandstone);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title {
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--cua-blue);
        }
        .section-body { padding: 20px; }
        .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        @media (max-width: 600px) { .field-grid { grid-template-columns: 1fr; } }

        /* ── Buttons ── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 28px;
            background: var(--cua-red);
            color: #fff;
            border: none; border-radius: 8px;
            font-family: 'Oswald', sans-serif;
            font-weight: 600; font-size: 14px;
            letter-spacing: 0.07em; text-transform: uppercase;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .btn-primary:hover { background: var(--cua-red-dark); }
        .btn-primary:active { transform: scale(0.97); }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 22px;
            background: transparent;
            color: var(--cua-blue);
            border: 1.5px solid var(--cua-blue);
            border-radius: 8px;
            font-family: 'Roboto', sans-serif;
            font-size: 14px; font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .btn-secondary:hover { background: var(--cua-blue); color: #fff; }

        /* ── Alert banners ── */
        .alert-success {
            background: #d1fae5; border: 1px solid #6ee7b7;
            color: #065f46; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 1.25rem;
            display: flex; align-items: center; gap: 10px;
            font-size: 14px;
        }
        .alert-error {
            background: #fee2e2; border: 1px solid #fca5a5;
            color: #991b1b; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 1.25rem;
            font-size: 14px;
        }
        .alert-info {
            background: #eff6ff; border: 1px solid #bfdbfe;
            color: #1e40af; border-radius: 8px;
            padding: 10px 14px; font-size: 13px;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .standing-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            background: rgba(10,50,85,0.1);
            color: var(--cua-blue);
        }
    </style>
</head>
<body>

{{-- ── HEADER ── --}}
<header style="background:var(--cua-blue); box-shadow:0 2px 12px rgba(0,0,0,0.22);">
    <div class="flex items-center justify-between px-5 h-[62px]">
        <a href="{{ route('chat') }}" class="flex items-center gap-3.5 no-underline">
            <img src="/images/busch_logo_white.png" alt="Busch School" class="h-9 w-auto">
            <div class="hidden sm:block leading-none">
                <p class="font-oswald font-semibold uppercase tracking-wide text-[16px] text-white leading-none"
                   style="letter-spacing:0.04em;">Course Planning Bot</p>
                <p class="text-[10px] mt-1 tracking-wider uppercase font-light"
                   style="color:rgba(255,255,255,0.45);">Busch School of Business &middot; CUA</p>
            </div>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('profile.academic') }}" class="text-[13px] px-3 py-1.5 rounded text-white transition-colors"
               onmouseover="this.style.background='rgba(255,255,255,0.1)'"
               onmouseout="this.style.background='transparent'">
                Academic Profile
            </a>
            <a href="{{ route('chat') }}"
               class="font-oswald font-semibold uppercase tracking-wide text-[12px] text-white px-4 py-1.5 rounded transition-colors"
               style="border:1px solid rgba(255,255,255,0.6); letter-spacing:0.07em;"
               onmouseover="this.style.borderColor='#fff'; this.style.background='rgba(255,255,255,0.1)'"
               onmouseout="this.style.borderColor='rgba(255,255,255,0.6)'; this.style.background='transparent'">
                Back to Chat
            </a>
        </div>
    </div>
    <div class="h-[2px]" style="background:linear-gradient(to right, var(--cua-gold) 0%, rgba(177,143,80,0.2) 60%, transparent 100%);"></div>
</header>

{{-- ── MAIN ── --}}
<main style="max-width:760px; margin:0 auto; padding:2rem 1.25rem 3rem;">

    {{-- Page title --}}
    <div class="mb-6">
        <h1 class="font-oswald font-bold text-[26px] text-[var(--cua-blue)] uppercase tracking-wide">Edit Academic Profile</h1>
        <p class="text-sm mt-1" style="color:var(--text-muted);">
            Update your degree program, specializations, and academic progress. The planning bot uses this information on every conversation.
        </p>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error">
            <strong>Please fix the following:</strong>
            <ul class="mt-1 ml-4 list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── FORM ── --}}
    <form method="POST" action="{{ route('profile.academic.update') }}"
          x-data="{
              degree: '{{ old('degree', $profile->degree) }}',
              catalogYear: '{{ old('catalog_year', $profile->catalog_year) }}',
              credits: {{ old('credits_completed', $profile->credits_completed ?? 0) }},
              spec1: '{{ old('specialization_1', $profile->specialization_1) }}',
              spec2: '{{ old('specialization_2', $profile->specialization_2) }}',
              spec3: '{{ old('specialization_3', $profile->specialization_3) }}',
              specsPost: {{ Js::from($specsPost) }},
              specsPre:  {{ Js::from($specsPre) }},
              get specs() {
                  return this.catalogYear === 'post_2024' ? this.specsPost : this.specsPre;
              },
              get standing() {
                  const c = parseInt(this.credits) || 0;
                  if (c >= 90) return 'Senior';
                  if (c >= 60) return 'Junior';
                  if (c >= 30) return 'Sophomore';
                  return 'Freshman';
              },
              get showSpecs() {
                  return this.degree === 'bsba';
              },
              init() {
                  // x-for renders options asynchronously; force-sync select values after first tick
                  this.$nextTick(() => {
                      if (this.$refs.spec1El) this.$refs.spec1El.value = this.spec1;
                      if (this.$refs.spec2El) this.$refs.spec2El.value = this.spec2;
                      if (this.$refs.spec3El) this.$refs.spec3El.value = this.spec3;
                  });
                  // When catalog year changes, reset spec selections (options change)
                  this.$watch('catalogYear', () => {
                      this.spec1 = '';
                      this.spec2 = '';
                      this.spec3 = '';
                  });
              },
          }">
        @csrf

        {{-- Section 1: Program Information --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="color:var(--cua-blue); flex-shrink:0;">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                </svg>
                <span class="section-title">Program Information</span>
            </div>
            <div class="section-body">

                {{-- Degree --}}
                <div class="mb-5">
                    <label class="field-label">Degree Program</label>
                    <div class="radio-group">
                        @foreach([
                            ['bsba',           'B.S.B.A.',                   'Bachelor of Science in Business Administration'],
                            ['bs_accounting',  'B.S. in Accounting',         'Standalone accounting degree'],
                            ['double_major',   'B.A. Double Major',          'Business as a second major'],
                            ['business_minor', 'Business Minor',             'Non-business students adding a minor'],
                        ] as [$val, $title, $sub])
                        <label class="radio-card">
                            <input type="radio" name="degree" value="{{ $val }}" x-model="degree">
                            <div>
                                <div class="radio-card-title">{{ $title }}</div>
                                <div class="radio-card-sub">{{ $sub }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('degree')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                {{-- Catalog Year --}}
                <div>
                    <label class="field-label">Catalog Year</label>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="catalog_year" value="post_2024" x-model="catalogYear">
                            <div>
                                <div class="radio-card-title">Post-2024</div>
                                <div class="radio-card-sub">Admitted Fall 2024 or later</div>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="catalog_year" value="pre_2024" x-model="catalogYear">
                            <div>
                                <div class="radio-card-title">Pre-2024</div>
                                <div class="radio-card-sub">Admitted Fall 2020 – Spring 2024</div>
                            </div>
                        </label>
                    </div>
                    @error('catalog_year')<p class="field-error">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        {{-- Section 2: Academic Status --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="color:var(--cua-blue); flex-shrink:0;">
                    <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm6.5 0h-1v6.5H0v1h5.5V16h1V9.5H12v-1H6.5V2z"/>
                </svg>
                <span class="section-title">Academic Status</span>
            </div>
            <div class="section-body">
                <div class="field-grid mb-5">

                    {{-- Credits completed --}}
                    <div>
                        <label for="credits_completed" class="field-label">Credits Completed</label>
                        <input type="number" id="credits_completed" name="credits_completed"
                               min="0" max="250"
                               x-model.number="credits"
                               value="{{ old('credits_completed', $profile->credits_completed) }}"
                               class="field-input {{ $errors->has('credits_completed') ? 'error' : '' }}">
                        @error('credits_completed')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Standing (derived, read-only) --}}
                    <div>
                        <label class="field-label">Academic Standing</label>
                        <div class="flex items-center h-[44px]">
                            <span class="standing-badge" x-text="standing"></span>
                            <span class="ml-2 text-xs" style="color:var(--text-muted);">auto-set from credits</span>
                        </div>
                    </div>

                    {{-- Admit term --}}
                    <div>
                        <label for="admit_term" class="field-label">Admit Term</label>
                        <select id="admit_term" name="admit_term"
                                class="field-input {{ $errors->has('admit_term') ? 'error' : '' }}">
                            @foreach($admitTerms as $term)
                                <option value="{{ $term }}" {{ old('admit_term', $profile->admit_term) === $term ? 'selected' : '' }}>
                                    {{ $term }}
                                </option>
                            @endforeach
                        </select>
                        @error('admit_term')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Expected graduation --}}
                    <div>
                        <label for="expected_graduation" class="field-label">Expected Graduation</label>
                        <select id="expected_graduation" name="expected_graduation"
                                class="field-input {{ $errors->has('expected_graduation') ? 'error' : '' }}">
                            @foreach($graduationTerms as $term)
                                <option value="{{ $term }}" {{ old('expected_graduation', $profile->expected_graduation) === $term ? 'selected' : '' }}>
                                    {{ $term }}
                                </option>
                            @endforeach
                        </select>
                        @error('expected_graduation')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 3: Specializations (BSBA only) --}}
        <div class="section-card" x-show="showSpecs" x-cloak>
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="color:var(--cua-blue); flex-shrink:0;">
                    <path d="M5.5 2A3.5 3.5 0 0 0 2 5.5v5A3.5 3.5 0 0 0 5.5 14h5a3.5 3.5 0 0 0 3.5-3.5V8a.5.5 0 0 1 1 0v2.5a4.5 4.5 0 0 1-4.5 4.5h-5A4.5 4.5 0 0 1 1 10.5v-5A4.5 4.5 0 0 1 5.5 1H8a.5.5 0 0 1 0 1H5.5z"/>
                    <path d="M16 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                </svg>
                <span class="section-title">Specializations</span>
            </div>
            <div class="section-body">

                <div class="alert-info mb-4" x-show="catalogYear !== ''" x-cloak>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink:0; margin-top:1px;">
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.45-.083.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                    <span>Showing specializations for your selected catalog year. Sports Management and Entrepreneurship require a double specialization.</span>
                </div>

                {{-- Primary specialization --}}
                <div class="mb-4">
                    <label for="specialization_1" class="field-label">Primary Specialization</label>
                    <select id="specialization_1" name="specialization_1" x-model="spec1" x-ref="spec1El"
                            class="field-input {{ $errors->has('specialization_1') ? 'error' : '' }}">
                        <option value="">— Select specialization —</option>
                        <template x-for="[key, label] in Object.entries(specs)" :key="key">
                            <option :value="key" x-text="label"></option>
                        </template>
                    </select>
                    @error('specialization_1')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                {{-- Second specialization --}}
                <div class="mb-4">
                    <label for="specialization_2" class="field-label">Second Specialization <span style="font-weight:400; text-transform:none; letter-spacing:0;">(optional)</span></label>
                    <select id="specialization_2" name="specialization_2" x-model="spec2" x-ref="spec2El"
                            class="field-input {{ $errors->has('specialization_2') ? 'error' : '' }}">
                        <option value="">— None —</option>
                        <template x-for="[key, label] in Object.entries(specs)" :key="key">
                            <option :value="key" x-text="label"></option>
                        </template>
                    </select>
                    @error('specialization_2')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                {{-- Third specialization --}}
                <div>
                    <label for="specialization_3" class="field-label">Third Specialization <span style="font-weight:400; text-transform:none; letter-spacing:0;">(optional)</span></label>
                    <select id="specialization_3" name="specialization_3" x-model="spec3" x-ref="spec3El"
                            class="field-input {{ $errors->has('specialization_3') ? 'error' : '' }}">
                        <option value="">— None —</option>
                        <template x-for="[key, label] in Object.entries(specs)" :key="key">
                            <option :value="key" x-text="label"></option>
                        </template>
                    </select>
                    @error('specialization_3')<p class="field-error">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        {{-- Note for non-BSBA degrees --}}
        <div x-show="!showSpecs" x-cloak class="mb-5">
            <div class="alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink:0; margin-top:1px;">
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.45-.083.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                </svg>
                <span x-show="degree === 'bs_accounting'">B.S. in Accounting students do not declare specializations — the accounting major is the program.</span>
                <span x-show="degree !== 'bs_accounting' && degree !== 'bsba'">Specializations apply to B.S.B.A. students. Double major and minor students follow a different course structure.</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-4 flex-wrap">
            <button type="submit" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
            </button>
            <a href="{{ route('profile.academic') }}" class="btn-secondary">Cancel</a>
        </div>

    </form>

    {{-- ── COURSES SECTION ── --}}
    @if(session('course_success'))
    <div class="alert-success mt-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
        {{ session('course_success') }}
    </div>
    @endif

    @php
        $v  = fn(string $k, string $d = 'not_yet') => old($k, $laData[$k] ?? $d);
        $vc = fn(string $k, string $d = 'not_yet') => old($k, $coreData[$k] ?? $d);
        $vs = fn(string $k) => old("spec_courses[{$k}]", $specData[$k] ?? 'not_yet');
        $coreOptions = $requirements[$catalogYear]['business_core_options'] ?? [];
        $sh = 'style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin:1rem 0 .4rem;"';
    @endphp

    <form method="POST" action="{{ route('profile.academic.courses') }}"
          x-data="{ toDelete: [], markDelete(id) { if (this.toDelete.includes(id)) { this.toDelete = this.toDelete.filter(x => x !== id); } else { this.toDelete.push(id); } }, isDeleted(id) { return this.toDelete.includes(id); } }">
        @csrf

        {{-- ============================================================ --}}
        {{-- LIBERAL ARTS                                                  --}}
        {{-- ============================================================ --}}
        <div class="section-card mt-6">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="color:var(--cua-blue);flex-shrink:0;"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.746c-.917-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/></svg>
                <span class="section-title">Liberal Arts Curriculum</span>
                <span style="margin-left:auto;font-size:12px;color:var(--text-muted);font-weight:500;">Select the course you completed for each slot</span>
            </div>
            <div class="section-body">

                <p {!! $sh !!}>Philosophy</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">Classical Philosophy</label>
                        <select name="la_classical_philosophy" class="field-input">
                            <option value="not_yet" {{ $v('la_classical_philosophy') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_classical_philosophy'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_classical_philosophy') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Modern Philosophy</label>
                        <select name="la_modern_philosophy" class="field-input">
                            <option value="not_yet" {{ $v('la_modern_philosophy') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_modern_philosophy'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_modern_philosophy') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Philosophy Elective <span style="font-weight:400;text-transform:none;letter-spacing:0;">(any PHIL / HSPH 203–204)</span></label>
                        <input type="text" name="la_phil_elective" class="field-input"
                               value="{{ old('la_phil_elective', $laData['la_phil_elective'] !== 'not_yet' ? ($laData['la_phil_elective'] ?? '') : '') }}"
                               placeholder="e.g. PHIL 301">
                    </div>
                </div>

                <p {!! $sh !!}>Theology</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">Theology I</label>
                        <select name="la_theology_1" class="field-input">
                            <option value="not_yet" {{ $v('la_theology_1') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_theology_1'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_theology_1') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Theology II</label>
                        <select name="la_theology_2" class="field-input">
                            <option value="not_yet" {{ $v('la_theology_2') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_theology_2'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_theology_2') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Theology Elective <span style="font-weight:400;text-transform:none;letter-spacing:0;">(any TRS / HSTR)</span></label>
                        <input type="text" name="la_theology_elective" class="field-input"
                               value="{{ old('la_theology_elective', $laData['la_theology_elective'] !== 'not_yet' ? ($laData['la_theology_elective'] ?? '') : '') }}"
                               placeholder="e.g. TRS 301">
                    </div>
                </div>

                <p {!! $sh !!}>Rhetoric &amp; Languages</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">Rhetoric &amp; Composition</label>
                        <select name="la_rhetoric" class="field-input">
                            <option value="not_yet" {{ $v('la_rhetoric') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_rhetoric'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_rhetoric') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Language I</label>
                        <select name="la_language_1" class="field-input">
                            <option value="not_yet" {{ $v('la_language_1') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_language_1'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_language_1') === $opt ? 'selected':'' }}>{{ $opt }}{{ $opt === 'SPAN 113' ? ' — fulfills I &amp; II' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Language II</label>
                        <select name="la_language_2" class="field-input">
                            <option value="not_yet" {{ $v('la_language_2') === 'not_yet' ? 'selected' : '' }}>Not yet / exempt via SPAN 113</option>
                            @foreach(array_slice($allowedLa['la_language_2'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_language_2') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <p {!! $sh !!}>Humanities &amp; Arts</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">Explorations in Literature</label>
                        <select name="la_literature" class="field-input">
                            <option value="not_yet" {{ $v('la_literature') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_literature'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_literature') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Explorations in Fine Arts</label>
                        <select name="la_fine_arts" class="field-input">
                            <option value="not_yet" {{ $v('la_fine_arts') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_fine_arts'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_fine_arts') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <p {!! $sh !!}>Social Science, History &amp; Natural Science</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">Foundations in Social Science</label>
                        <select name="la_social_science" class="field-input">
                            <option value="not_yet" {{ $v('la_social_science') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_social_science'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_social_science') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Foundations in History or Politics</label>
                        <select name="la_history_politics" class="field-input">
                            <option value="not_yet" {{ $v('la_history_politics') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_history_politics'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_history_politics') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Foundations in Natural Science</label>
                        <select name="la_natural_science" class="field-input">
                            <option value="not_yet" {{ $v('la_natural_science') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_natural_science'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_natural_science') === $opt ? 'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Mathematical Thinking</label>
                        <select name="la_math_thinking" class="field-input">
                            <option value="not_yet" {{ $v('la_math_thinking') === 'not_yet' ? 'selected' : '' }}>Not yet completed</option>
                            @foreach(array_slice($allowedLa['la_math_thinking'],1) as $opt)
                            <option value="{{ $opt }}" {{ $v('la_math_thinking') === $opt ? 'selected':'' }}>{{ $opt === 'math_exempt' ? 'Math Placement Exempt' : $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- BUSINESS CORE                                                 --}}
        {{-- ============================================================ --}}
        <div class="section-card mt-6">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="color:var(--cua-blue);flex-shrink:0;"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zm0 3h16v5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V7zm3 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1H3zm2.5 0a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5z"/></svg>
                <span class="section-title">Business Core Curriculum</span>
            </div>
            <div class="section-body">

                <p {!! $sh !!}>1. Business Foundations</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">ENT 118 — Vocation of Business</label>
                        <select name="core_ent118" class="field-input">
                            <option value="not_yet"  {{ $vc('core_ent118') === 'not_yet'  ? 'selected':'' }}>Not yet completed</option>
                            <option value="ENT 118A" {{ $vc('core_ent118') === 'ENT 118A' ? 'selected':'' }}>ENT 118A (Fall)</option>
                            <option value="ENT 118B" {{ $vc('core_ent118') === 'ENT 118B' ? 'selected':'' }}>ENT 118B (Spring)</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">MGT 123 — Foundations of Business</label>
                        <select name="core_mgt123" class="field-input">
                            <option value="not_yet"  {{ $vc('core_mgt123') === 'not_yet'  ? 'selected':'' }}>Not yet completed</option>
                            <option value="MGT 123A" {{ $vc('core_mgt123') === 'MGT 123A' ? 'selected':'' }}>MGT 123A (Spring, freshmen)</option>
                            <option value="MGT 123B" {{ $vc('core_mgt123') === 'MGT 123B' ? 'selected':'' }}>MGT 123B (Fall/Spring, soph+)</option>
                        </select>
                    </div>
                </div>

                <p {!! $sh !!}>2. Economic Thought</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">SRES 101 — Markets &amp; Prosperity I</label>
                        <select name="core_sres101" class="field-input">
                            <option value="not_yet"   {{ $vc('core_sres101') === 'not_yet'   ? 'selected':'' }}>Not yet completed</option>
                            <option value="SRES 101"  {{ $vc('core_sres101') === 'SRES 101'  ? 'selected':'' }}>SRES 101</option>
                            <option value="SRES 101H" {{ $vc('core_sres101') === 'SRES 101H' ? 'selected':'' }}>SRES 101H (Honors)</option>
                            <option value="ECON 100"  {{ $vc('core_sres101') === 'ECON 100'  ? 'selected':'' }}>ECON 100 (equivalent)</option>
                            <option value="ECON 102"  {{ $vc('core_sres101') === 'ECON 102'  ? 'selected':'' }}>ECON 102 (equivalent)</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">SRES 102 — Markets &amp; Prosperity II</label>
                        <select name="core_sres102" class="field-input">
                            <option value="not_yet"  {{ $vc('core_sres102') === 'not_yet'  ? 'selected':'' }}>Not yet completed</option>
                            <option value="SRES 102" {{ $vc('core_sres102') === 'SRES 102' ? 'selected':'' }}>SRES 102</option>
                            <option value="ECON 200" {{ $vc('core_sres102') === 'ECON 200' ? 'selected':'' }}>ECON 200 (equivalent)</option>
                            <option value="ECON 101" {{ $vc('core_sres102') === 'ECON 101' ? 'selected':'' }}>ECON 101 (equivalent)</option>
                        </select>
                    </div>
                    @if($isPost2024)
                    <div>
                        <label class="field-label">SRES 290 — Catholic Social Thought in Business</label>
                        <select name="core_sres290" class="field-input">
                            <option value="not_yet"     {{ $vc('core_sres290') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_sres290') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="Completed"   {{ $vc('core_sres290') === 'Completed'   ? 'selected':'' }}>Completed</option>
                        </select>
                    </div>
                    @endif
                </div>

                <p {!! $sh !!}>3. Accounting</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">ACCT 205 — Financial Accounting</label>
                        <select name="core_acct205" class="field-input">
                            <option value="not_yet"     {{ $vc('core_acct205') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_acct205') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="Completed"   {{ $vc('core_acct205') === 'Completed'   ? 'selected':'' }}>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">ACCT 206 — Managerial Accounting</label>
                        <select name="core_acct206" class="field-input">
                            <option value="not_yet"     {{ $vc('core_acct206') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_acct206') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="Completed"   {{ $vc('core_acct206') === 'Completed'   ? 'selected':'' }}>Completed</option>
                        </select>
                    </div>
                </div>

                <p {!! $sh !!}>4. Finance, Marketing &amp; Communications</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">FIN 226 — Introduction to Finance</label>
                        <select name="core_fin226" class="field-input">
                            <option value="not_yet"     {{ $vc('core_fin226') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_fin226') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="Completed"   {{ $vc('core_fin226') === 'Completed'   ? 'selected':'' }}>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">MGT 250 — Business Communications</label>
                        <select name="core_mgt250" class="field-input">
                            <option value="not_yet"     {{ $vc('core_mgt250') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_mgt250') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="Completed"   {{ $vc('core_mgt250') === 'Completed'   ? 'selected':'' }}>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">MKT 345 — Marketing Management</label>
                        <select name="core_mkt345" class="field-input">
                            <option value="not_yet"     {{ $vc('core_mkt345') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_mkt345') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="MKT 345"     {{ $vc('core_mkt345') === 'MKT 345'     ? 'selected':'' }}>MKT 345 (completed)</option>
                        </select>
                    </div>
                </div>

                <p {!! $sh !!}>5. Mathematics &amp; Statistics</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">Mathematics</label>
                        <select name="core_math" class="field-input">
                            <option value="not_yet"             {{ $vc('core_math') === 'not_yet'             ? 'selected':'' }}>Not yet completed</option>
                            <option value="MATH 110"            {{ $vc('core_math') === 'MATH 110'            ? 'selected':'' }}>MATH 110</option>
                            <option value="MATH 111"            {{ $vc('core_math') === 'MATH 111'            ? 'selected':'' }}>MATH 111</option>
                            <option value="Level 1 or 2 Exempt" {{ $vc('core_math') === 'Level 1 or 2 Exempt' ? 'selected':'' }}>Level 1 or 2 Placement Exempt</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Statistics for Business</label>
                        <select name="core_stats" class="field-input">
                            <option value="not_yet"  {{ $vc('core_stats') === 'not_yet'  ? 'selected':'' }}>Not yet completed</option>
                            <option value="MGT 265"  {{ $vc('core_stats') === 'MGT 265'  ? 'selected':'' }}>MGT 265</option>
                            @if(!$isPost2024)
                            <option value="ECON 223" {{ $vc('core_stats') === 'ECON 223' ? 'selected':'' }}>ECON 223 (Pre-2024)</option>
                            @endif
                        </select>
                    </div>
                    @if(!$isPost2024)
                    <div>
                        <label class="field-label">MGT 365 — Quantitative Methods <span style="font-weight:400;text-transform:none;letter-spacing:0;">(Pre-2024)</span></label>
                        <select name="core_mgt365" class="field-input">
                            <option value="not_yet"     {{ $vc('core_mgt365') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_mgt365') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="MGT 365"     {{ $vc('core_mgt365') === 'MGT 365'     ? 'selected':'' }}>MGT 365 (completed)</option>
                            <option value="BUS 603"     {{ $vc('core_mgt365') === 'BUS 603'     ? 'selected':'' }}>BUS 603 (equivalent)</option>
                        </select>
                    </div>
                    @endif
                </div>

                <p {!! $sh !!}>6. Information Management Gateway</p>
                <div class="mb-4" style="max-width:340px;">
                    <label class="field-label">Info Management Course</label>
                    <select name="core_info_gateway" class="field-input">
                        <option value="not_yet" {{ $vc('core_info_gateway') === 'not_yet' ? 'selected':'' }}>Not yet completed</option>
                        @foreach($coreOptions['info_gateway'] ?? [] as $c)
                        <option value="{{ $c }}" {{ $vc('core_info_gateway') === $c ? 'selected':'' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <p {!! $sh !!}>7. Ethics &amp; Law</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">Business Ethics</label>
                        <select name="core_ethics" class="field-input">
                            <option value="not_yet" {{ $vc('core_ethics') === 'not_yet' ? 'selected':'' }}>Not yet completed</option>
                            @foreach($coreOptions['ethics'] ?? [] as $c)
                            <option value="{{ $c }}" {{ $vc('core_ethics') === $c ? 'selected':'' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Business Law</label>
                        <select name="core_law" class="field-input">
                            <option value="not_yet" {{ $vc('core_law') === 'not_yet' ? 'selected':'' }}>Not yet completed</option>
                            @foreach($coreOptions['law'] ?? [] as $opt)
                                @php $code = is_array($opt) ? $opt['code'] : $opt; $label = is_array($opt) ? $opt['label'] : $opt; @endphp
                                <option value="{{ $code }}" {{ $vc('core_law') === $code ? 'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <p {!! $sh !!}>8. Career Discernment</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">BUS 199 — Career Discernment I <span style="font-weight:400;text-transform:none;letter-spacing:0;">(Fall only)</span></label>
                        <select name="core_bus199" class="field-input">
                            <option value="not_yet"     {{ $vc('core_bus199') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_bus199') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="Completed"   {{ $vc('core_bus199') === 'Completed'   ? 'selected':'' }}>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Career Discernment II{{ $isSales ? ' — BUS 299A / MKT 299' : ' — BUS 299A' }}</label>
                        <select name="core_bus299a" class="field-input">
                            <option value="not_yet"     {{ $vc('core_bus299a') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_bus299a') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="BUS 299A"    {{ $vc('core_bus299a') === 'BUS 299A'    ? 'selected':'' }}>BUS 299A (completed)</option>
                            @if($isSales)
                            <option value="MKT 299"     {{ $vc('core_bus299a') === 'MKT 299'     ? 'selected':'' }}>MKT 299 — Sales (completed)</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Career Discernment III{{ $isSales ? ' — BUS 399A / MKT 399' : ' — BUS 399A' }}</label>
                        <select name="core_bus399a" class="field-input">
                            <option value="not_yet"     {{ $vc('core_bus399a') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_bus399a') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="BUS 399A"    {{ $vc('core_bus399a') === 'BUS 399A'    ? 'selected':'' }}>BUS 399A (completed)</option>
                            @if($isSales)
                            <option value="MKT 399"     {{ $vc('core_bus399a') === 'MKT 399'     ? 'selected':'' }}>MKT 399 — Sales (completed)</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Career Discernment IV{{ $isSales ? ' — BUS 499A / MKT 499' : ' — BUS 499A' }}</label>
                        <select name="core_bus499a" class="field-input">
                            <option value="not_yet"     {{ $vc('core_bus499a') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_bus499a') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="BUS 499A"    {{ $vc('core_bus499a') === 'BUS 499A'    ? 'selected':'' }}>BUS 499A (completed)</option>
                            @if($isSales)
                            <option value="MKT 499"     {{ $vc('core_bus499a') === 'MKT 499'     ? 'selected':'' }}>MKT 499 — Sales (completed)</option>
                            @endif
                        </select>
                    </div>
                </div>

                <p {!! $sh !!}>9. Strategy &amp; Assessment</p>
                <div class="field-grid mb-4">
                    <div>
                        <label class="field-label">MGT 475 — Business Strategy</label>
                        <select name="core_mgt475" class="field-input">
                            <option value="not_yet"     {{ $vc('core_mgt475') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_mgt475') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="Completed"   {{ $vc('core_mgt475') === 'Completed'   ? 'selected':'' }}>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">BUS 498 — Comprehensive Assessment</label>
                        <select name="core_bus498" class="field-input">
                            <option value="not_yet"     {{ $vc('core_bus498') === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                            <option value="in_progress" {{ $vc('core_bus498') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="Completed"   {{ $vc('core_bus498') === 'Completed'   ? 'selected':'' }}>Completed</option>
                        </select>
                    </div>
                </div>

                @if(!$isPost2024 && $isSingleSpec)
                <p {!! $sh !!}>10. Business Electives <span style="font-weight:400;text-transform:none;letter-spacing:0;">(Pre-2024 single-spec only)</span></p>
                <div class="field-grid mb-2">
                    <div>
                        <label class="field-label">Business Elective 1</label>
                        <input type="text" name="core_elective_1" class="field-input"
                               value="{{ old('core_elective_1', $coreData['core_elective_1'] ?? '') }}"
                               placeholder="e.g. MGT 302">
                    </div>
                    <div>
                        <label class="field-label">Business Elective 2</label>
                        <input type="text" name="core_elective_2" class="field-input"
                               value="{{ old('core_elective_2', $coreData['core_elective_2'] ?? '') }}"
                               placeholder="e.g. ECON 301">
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SPECIALIZATION COURSES                                        --}}
        {{-- ============================================================ --}}
        @if(!empty($selectedSpecs) && $profile->degree === 'bsba')
        <div class="section-card mt-6">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="color:var(--cua-blue);flex-shrink:0;"><path d="M5.5 2A3.5 3.5 0 0 0 2 5.5v5A3.5 3.5 0 0 0 5.5 14h5a3.5 3.5 0 0 0 3.5-3.5V8a.5.5 0 0 1 1 0v2.5a4.5 4.5 0 0 1-4.5 4.5h-5A4.5 4.5 0 0 1 1 10.5v-5A4.5 4.5 0 0 1 5.5 1H8a.5.5 0 0 1 0 1H5.5z"/><path d="M16 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                <span class="section-title">Specialization Courses</span>
            </div>
            <div class="section-body">
                @foreach($selectedSpecs as $specKey)
                    @php $spec = $allSpecs[$specKey] ?? null; @endphp
                    @if(!$spec) @continue @endif
                    <div style="margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border-light);">
                        <p style="font-family:'Oswald',sans-serif;font-size:1rem;font-weight:700;color:var(--cua-blue);margin:0 0 .75rem;">{{ $spec['name'] }}</p>
                        @if(!empty($spec['notes']))
                            <div class="alert-info mb-3" style="font-size:12px;">{{ $spec['notes'] }}</div>
                        @endif
                        @if(!empty($spec['required']))
                            <p {!! $sh !!}>Required Courses</p>
                            @foreach($spec['required'] as $code)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #f5f5f5;gap:1rem;">
                                <span style="font-weight:600;font-size:.9rem;color:#1a1a1a;min-width:90px;flex-shrink:0;">{{ $code }}</span>
                                <select name="spec_courses[{{ $code }}]" style="width:200px;flex-shrink:0;padding:.4rem .7rem;border:1.5px solid var(--border);border-radius:6px;font-size:.88rem;font-family:'Roboto',sans-serif;background:#fff;">
                                    <option value="not_yet"     {{ $vs($code) === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                                    <option value="in_progress" {{ $vs($code) === 'in_progress' ? 'selected':'' }}>In Progress</option>
                                    <option value="completed"   {{ $vs($code) === 'completed'   ? 'selected':'' }}>Completed</option>
                                </select>
                            </div>
                            @endforeach
                        @endif
                        @if(!empty($spec['elective_groups']))
                            @foreach($spec['elective_groups'] as $group)
                            <p {!! $sh !!}>{{ $group['description'] ?? 'Electives' }} (choose {{ $group['choose_count'] ?? 1 }})</p>
                            @foreach($group['courses'] as $code)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #f5f5f5;gap:1rem;">
                                <span style="font-weight:600;font-size:.9rem;color:#1a1a1a;min-width:90px;flex-shrink:0;">{{ $code }}</span>
                                <select name="spec_courses[{{ $code }}]" style="width:200px;flex-shrink:0;padding:.4rem .7rem;border:1.5px solid var(--border);border-radius:6px;font-size:.88rem;font-family:'Roboto',sans-serif;background:#fff;">
                                    <option value="not_yet"     {{ $vs($code) === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                                    <option value="in_progress" {{ $vs($code) === 'in_progress' ? 'selected':'' }}>In Progress</option>
                                    <option value="completed"   {{ $vs($code) === 'completed'   ? 'selected':'' }}>Completed</option>
                                </select>
                            </div>
                            @endforeach
                            @endforeach
                        @elseif(!empty($spec['electives']) && ($spec['choose_count'] ?? 0) > 0)
                            <p {!! $sh !!}>Electives (choose {{ $spec['choose_count'] }})</p>
                            @foreach($spec['electives'] as $code)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #f5f5f5;gap:1rem;">
                                <span style="font-weight:600;font-size:.9rem;color:#1a1a1a;min-width:90px;flex-shrink:0;">{{ $code }}</span>
                                <select name="spec_courses[{{ $code }}]" style="width:200px;flex-shrink:0;padding:.4rem .7rem;border:1.5px solid var(--border);border-radius:6px;font-size:.88rem;font-family:'Roboto',sans-serif;background:#fff;">
                                    <option value="not_yet"     {{ $vs($code) === 'not_yet'     ? 'selected':'' }}>Not yet completed</option>
                                    <option value="in_progress" {{ $vs($code) === 'in_progress' ? 'selected':'' }}>In Progress</option>
                                    <option value="completed"   {{ $vs($code) === 'completed'   ? 'selected':'' }}>Completed</option>
                                </select>
                            </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ============================================================ --}}
        {{-- OTHER COURSES (transfer credits, misc)                        --}}
        {{-- ============================================================ --}}
        <div class="section-card mt-6">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" style="color:var(--cua-blue);flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                <span class="section-title">Other Courses</span>
                <span style="margin-left:auto;font-size:12px;color:var(--text-muted);font-weight:500;">Transfer credits, in-progress, and manually added courses</span>
            </div>

            @if($courses->isEmpty())
            <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:13px;">No other courses on record.</div>
            @else
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                    <thead>
                        <tr style="background:#faf8f6;border-bottom:1px solid var(--border-light);">
                            <th style="padding:9px 16px;text-align:left;font-size:11px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);">Code</th>
                            <th style="padding:9px 12px;text-align:left;font-size:11px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);">Name</th>
                            <th style="padding:9px 12px;text-align:left;font-size:11px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);">Category</th>
                            <th style="padding:9px 12px;text-align:left;font-size:11px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);">Status</th>
                            <th style="padding:9px 12px;width:44px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                        <tr x-bind:style="isDeleted({{ $course->id }}) ? 'opacity:0.35;background:#fff1f2;' : ''"
                            style="border-bottom:1px solid var(--border-light);transition:opacity .15s,background .15s;">
                            <td style="padding:9px 16px;font-weight:600;color:#1a1a1a;white-space:nowrap;">{{ $course->course_code }}</td>
                            <td style="padding:9px 12px;color:#374151;font-size:12.5px;max-width:200px;">{{ $course->course_name !== $course->course_code ? $course->course_name : '—' }}</td>
                            <td style="padding:9px 12px;font-size:12px;color:var(--text-muted);">{{ str_replace('_', ' ', $course->requirement_category) }}</td>
                            <td style="padding:9px 12px;">
                                <span style="font-size:12px;padding:2px 8px;border-radius:12px;background:{{ $course->status==='completed' ? '#d1fae5' : ($course->status==='in_progress' ? '#dbeafe' : '#f3f4f6') }};color:{{ $course->status==='completed' ? '#065f46' : ($course->status==='in_progress' ? '#1e40af' : '#6b7280') }};">
                                    {{ ucfirst(str_replace('_',' ',$course->status)) }}
                                </span>
                            </td>
                            <td style="padding:9px 12px;text-align:center;">
                                <input type="hidden" :name="isDeleted({{ $course->id }}) ? 'delete_courses[]' : '_ignore'" value="{{ $course->id }}">
                                <button type="button" @click="markDelete({{ $course->id }})"
                                        :title="isDeleted({{ $course->id }}) ? 'Undo remove' : 'Remove'"
                                        style="width:28px;height:28px;border:none;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;"
                                        :style="isDeleted({{ $course->id }}) ? 'background:#fee2e2;color:#dc2626;' : 'background:#f3f4f6;color:#9ca3af;'">
                                    <template x-if="!isDeleted({{ $course->id }})"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></template>
                                    <template x-if="isDeleted({{ $course->id }})"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg></template>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <div style="padding:16px 20px;border-top:2px solid var(--border-light);background:var(--sandstone);">
                <p style="font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px;">Add a Course</p>
                <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Course Code *</label>
                        <input type="text" name="new_course_code" value="{{ old('new_course_code') }}" placeholder="e.g. ACCT 205" maxlength="20"
                               style="width:130px;font-size:13px;padding:7px 10px;border:1.5px solid var(--border);border-radius:7px;text-transform:uppercase;background:#fff;">
                        @error('new_course_code')<p style="margin-top:4px;font-size:11.5px;color:#dc2626;">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Status</label>
                        <select name="new_course_status" style="font-size:13px;padding:7px 28px 7px 10px;border:1.5px solid var(--border);border-radius:7px;background-color:#fff;appearance:none;">
                            <option value="completed" {{ old('new_course_status','completed') === 'completed' ? 'selected':'' }}>Completed</option>
                            <option value="in_progress" {{ old('new_course_status') === 'in_progress' ? 'selected':'' }}>In Progress</option>
                            <option value="not_yet">Not Yet</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Grade</label>
                        <input type="text" name="new_course_grade" value="{{ old('new_course_grade') }}" placeholder="A, B+…" maxlength="5"
                               style="width:72px;font-size:13px;padding:7px 10px;border:1.5px solid var(--border);border-radius:7px;text-transform:uppercase;background:#fff;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;">Semester</label>
                        <input type="text" name="new_course_semester" value="{{ old('new_course_semester') }}" placeholder="Fall 2024" maxlength="30" list="semester-options"
                               style="width:130px;font-size:13px;padding:7px 10px;border:1.5px solid var(--border);border-radius:7px;background:#fff;">
                    </div>
                </div>
                <p style="margin-top:8px;font-size:11.5px;color:var(--text-muted);">Use this for transfer credits or any course not covered by the sections above (format: DEPT 123).</p>
            </div>
        </div>

        <datalist id="semester-options">
            @foreach(['Fall 2020','Spring 2021','Fall 2021','Spring 2022','Fall 2022','Spring 2023','Fall 2023','Spring 2024','Fall 2024','Spring 2025','Fall 2025','Spring 2026','Fall 2026','Spring 2027'] as $t)
            <option value="{{ $t }}">
            @endforeach
        </datalist>

        <div class="flex items-center gap-4 flex-wrap mt-6">
            <button type="submit" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save All Course Changes
            </button>
        </div>

    </form>

</main>

<footer class="py-3 px-6 text-center text-xs font-light tracking-wide" style="background:#071e38; color:rgba(255,255,255,0.7);">
    AI guidance is informational. Always verify with a <a href="https://business.catholic.edu/academics/academic-services/index.html" target="_blank" class="underline">human advisor</a> before finalizing your schedule.
</footer>

</body>
</html>
