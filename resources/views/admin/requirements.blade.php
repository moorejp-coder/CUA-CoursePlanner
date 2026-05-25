@extends('admin.layout')

@section('title', 'Degree Requirements')
@section('heading', 'Degree Requirements')

@section('content')

@php
    $lines = fn (array $arr): string => implode("\n", $arr);
    $specLines = fn (array $spec): string => implode("\n", $spec['courses'] ?? []);

    $pre  = $requirements['pre_2024']  ?? [];
    $post = $requirements['post_2024'] ?? [];
    $la   = $requirements['liberal_arts'] ?? [];

    $preSpecs  = $pre['specializations']  ?? [];
    $postSpecs = $post['specializations'] ?? [];
@endphp

{{-- Warning banner --}}
<div style="display:flex;align-items:center;gap:10px;background:#fffbea;border:1px solid #C9A84C;border-radius:8px;padding:0.85rem 1.25rem;margin-bottom:1.5rem;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2" style="flex-shrink:0;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
    </svg>
    <div>
        <strong style="color:#6b4d00;font-size:13.5px;">Changes here immediately affect student onboarding and bot responses.</strong>
        <span style="color:#7c5c00;font-size:13px;margin-left:4px;">Review carefully before saving.</span>
    </div>
</div>

<form method="POST" action="{{ route('admin.requirements.save') }}" x-data="{ tab: 'post_2024' }">
@csrf

{{-- Top save button --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
    <div style="display:flex;gap:6px;">
        <button type="button"
            @click="tab = 'post_2024'"
            :class="tab === 'post_2024'
                ? 'tab-btn tab-btn-active'
                : 'tab-btn'"
            style="font-family:'Oswald',sans-serif;font-size:13.5px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;padding:8px 20px;border-radius:6px 6px 0 0;border:1.5px solid var(--border);cursor:pointer;transition:all 0.15s;"
            :style="tab === 'post_2024'
                ? 'background:var(--navy);color:#fff;border-color:var(--navy);'
                : 'background:#fff;color:var(--navy);'">
            Post-2024 Catalog
        </button>
        <button type="button"
            @click="tab = 'pre_2024'"
            :style="tab === 'pre_2024'
                ? 'background:var(--navy);color:#fff;border-color:var(--navy);'
                : 'background:#fff;color:var(--navy);'"
            style="font-family:'Oswald',sans-serif;font-size:13.5px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;padding:8px 20px;border-radius:6px 6px 0 0;border:1.5px solid var(--border);cursor:pointer;transition:all 0.15s;">
            Pre-2024 Catalog
        </button>
    </div>
    <button type="submit" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V7l-4-4zM12 17v-6M9 17v-3"/>
        </svg>
        Save All Changes
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════
     POST-2024 TAB
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'post_2024'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

    {{-- Business Core --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <span class="card-title">BSBA Business Core — Post-2024</span>
            <span style="font-size:11.5px;color:#6b7280;">One requirement per line</span>
        </div>
        <div class="card-body">
            <p style="font-size:12px;color:#6b7280;margin:0 0 0.6rem;">Spring 2024 and after. Includes SRES 290, MGT 265 (not ECON 223), no MGT 365, no Quantitative Methods.</p>
            <textarea name="data[post_2024][business_core]" rows="14" style="font-family:monospace;font-size:12.5px;resize:vertical;line-height:1.7;">{{ $lines($post['business_core'] ?? []) }}</textarea>
        </div>
    </div>

    {{-- BS Accounting --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <span class="card-title">BS Accounting Requirements — Post-2024</span>
            <span style="font-size:11.5px;color:#6b7280;">One course per line</span>
        </div>
        <div class="card-body">
            <p style="font-size:12px;color:#6b7280;margin:0 0 0.6rem;">Post-2024: No ACCT 312. Includes elective (ACCT 480, ACCT 491, or ECON 370).</p>
            <textarea name="data[post_2024][bs_accounting]" rows="10" style="font-family:monospace;font-size:12.5px;resize:vertical;line-height:1.7;">{{ $lines($post['bs_accounting'] ?? []) }}</textarea>
        </div>
    </div>

    {{-- Post-2024 Specializations --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <span class="card-title">BSBA Specializations — Post-2024</span>
            <span style="font-size:11.5px;color:#6b7280;">Edit courses for each specialization below</span>
        </div>
        <div class="card-body" style="padding:0;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;border-top:1px solid var(--border);">
                @foreach($postSpecs as $slug => $spec)
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);{{ $loop->index % 2 === 0 ? 'border-right:1px solid var(--border);' : '' }}">
                    <p style="font-family:'Oswald',sans-serif;font-size:12.5px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;color:var(--navy);margin:0 0 0.4rem;">
                        {{ $spec['label'] }}
                    </p>
                    <textarea
                        name="data[post_2024][spec][{{ $slug }}]"
                        rows="7"
                        style="font-family:monospace;font-size:11.5px;resize:vertical;line-height:1.65;"
                    >{{ $specLines($spec) }}</textarea>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     PRE-2024 TAB
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'pre_2024'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none;">

    {{-- Business Core --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <span class="card-title">BSBA Business Core — Pre-2024</span>
            <span style="font-size:11.5px;color:#6b7280;">One requirement per line</span>
        </div>
        <div class="card-body">
            <p style="font-size:12px;color:#6b7280;margin:0 0 0.6rem;">Fall 2020 – Spring 2024. Includes ECON 223/MGT 265, MGT 365, Quantitative Methods. No SRES 290.</p>
            <textarea name="data[pre_2024][business_core]" rows="16" style="font-family:monospace;font-size:12.5px;resize:vertical;line-height:1.7;">{{ $lines($pre['business_core'] ?? []) }}</textarea>
        </div>
    </div>

    {{-- BS Accounting --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <span class="card-title">BS Accounting Requirements — Pre-2024</span>
            <span style="font-size:11.5px;color:#6b7280;">One course per line</span>
        </div>
        <div class="card-body">
            <p style="font-size:12px;color:#6b7280;margin:0 0 0.6rem;">Pre-2024: Includes ACCT 312. No accounting elective slot.</p>
            <textarea name="data[pre_2024][bs_accounting]" rows="9" style="font-family:monospace;font-size:12.5px;resize:vertical;line-height:1.7;">{{ $lines($pre['bs_accounting'] ?? []) }}</textarea>
        </div>
    </div>

    {{-- Pre-2024 Specializations --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <span class="card-title">BSBA Specializations — Pre-2024</span>
            <span style="font-size:11.5px;color:#6b7280;">Edit courses for each specialization below</span>
        </div>
        <div class="card-body" style="padding:0;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;border-top:1px solid var(--border);">
                @foreach($preSpecs as $slug => $spec)
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);{{ $loop->index % 2 === 0 ? 'border-right:1px solid var(--border);' : '' }}">
                    <p style="font-family:'Oswald',sans-serif;font-size:12.5px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;color:var(--navy);margin:0 0 0.4rem;">
                        {{ $spec['label'] }}
                    </p>
                    <textarea
                        name="data[pre_2024][spec][{{ $slug }}]"
                        rows="7"
                        style="font-family:monospace;font-size:11.5px;resize:vertical;line-height:1.65;"
                    >{{ $specLines($spec) }}</textarea>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     LIBERAL ARTS — shared for both catalogs
══════════════════════════════════════════════════════════ --}}
<div class="card" style="margin-bottom:1.25rem;border-top:3px solid var(--gold);">
    <div class="card-header">
        <span class="card-title">Liberal Arts Requirements</span>
        <span style="font-size:11.5px;color:#6b7280;font-weight:500;">Same for all catalog years &amp; degree programs</span>
    </div>
    <div class="card-body">
        <p style="font-size:12px;color:#6b7280;margin:0 0 0.6rem;">15 slots — one requirement per line. These apply to both BSBA and BS Accounting students, Pre-2024 and Post-2024.</p>
        <textarea name="data[liberal_arts]" rows="16" style="font-family:monospace;font-size:12.5px;resize:vertical;line-height:1.7;">{{ $lines($la) }}</textarea>
    </div>
</div>

{{-- Bottom save button --}}
<div style="display:flex;justify-content:flex-end;margin-top:0.5rem;padding-top:1rem;border-top:1px solid var(--border);">
    <button type="submit" class="btn btn-primary" style="padding:10px 28px;font-size:14px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V7l-4-4zM12 17v-6M9 17v-3"/>
        </svg>
        Save All Changes
    </button>
</div>

</form>

@endsection
