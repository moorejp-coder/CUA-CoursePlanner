@extends('admin.layout')

@section('title', 'Statistics')
@section('heading', 'Usage Statistics')

@section('content')

<div class="stat-cards">
    <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value">{{ number_format($totalUsers) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">With Profile</div>
        <div class="stat-value">{{ number_format($totalWithProfile) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Profile Rate</div>
        <div class="stat-value">{{ $totalUsers > 0 ? round($totalWithProfile / $totalUsers * 100) : 0 }}%</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

    {{-- Degree Breakdown --}}
    <div class="card">
        <div class="card-header"><span class="card-title">By Degree Program</span></div>
        @if($degreeBreakdown->isEmpty())
            <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:13px;">No data yet.</div>
        @else
        <table>
            <thead>
                <tr><th>Degree</th><th style="text-align:right;">Students</th><th style="text-align:right;">Share</th></tr>
            </thead>
            <tbody>
                @foreach($degreeBreakdown->sortDesc() as $degree => $count)
                <tr>
                    <td>{{ $degree ?: 'Unknown' }}</td>
                    <td style="text-align:right;font-family:'Oswald',sans-serif;font-size:18px;color:var(--navy);">{{ $count }}</td>
                    <td style="text-align:right;font-size:12px;color:#6b7280;">
                        {{ $totalWithProfile > 0 ? round($count / $totalWithProfile * 100) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Specialization Breakdown --}}
    <div class="card">
        <div class="card-header"><span class="card-title">By Specialization</span></div>
        @if($specBreakdown->isEmpty())
            <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:13px;">No data yet.</div>
        @else
        <table>
            <thead>
                <tr><th>Specialization</th><th style="text-align:right;">Students</th><th style="text-align:right;">Share</th></tr>
            </thead>
            <tbody>
                @foreach($specBreakdown as $spec => $count)
                <tr>
                    <td>{{ $spec ?: 'Unknown' }}</td>
                    <td style="text-align:right;font-family:'Oswald',sans-serif;font-size:18px;color:var(--navy);">{{ $count }}</td>
                    <td style="text-align:right;font-size:12px;color:#6b7280;">
                        {{ $totalWithProfile > 0 ? round($count / $totalWithProfile * 100) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>

{{-- Monthly Registrations --}}
<div class="card" style="margin-top:1.25rem;">
    <div class="card-header"><span class="card-title">Registrations — Last 6 Months</span></div>
    <div class="card-body">
        @if($registrationsByMonth->isEmpty())
            <p style="text-align:center;color:#9ca3af;font-size:13px;margin:0;">No registrations in the past 6 months.</p>
        @else
        @php $maxCount = $registrationsByMonth->max() ?: 1; @endphp
        <div style="display:flex;align-items:flex-end;gap:0.75rem;height:160px;padding-bottom:1.5rem;position:relative;">
            @foreach($registrationsByMonth as $month => $count)
            @php $pct = round($count / $maxCount * 100); @endphp
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;">
                <span style="font-size:11px;color:var(--navy);font-weight:600;margin-bottom:3px;">{{ $count }}</span>
                <div style="width:100%;background:var(--navy);border-radius:4px 4px 0 0;height:{{ max(4, $pct) }}%;opacity:0.85;"></div>
                <span style="font-size:10px;color:#6b7280;margin-top:4px;white-space:nowrap;">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@endsection
