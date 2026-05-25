@extends('admin.layout')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')

<div class="stat-cards">
    <div class="stat-card">
        <div class="stat-label">Total Students</div>
        <div class="stat-value">{{ number_format($totalStudents) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Profiles Created</div>
        <div class="stat-value">{{ number_format($totalWithProfile) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Registered Today</div>
        <div class="stat-value">{{ number_format($registeredToday) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Profile Rate</div>
        <div class="stat-value">{{ $totalStudents > 0 ? round($totalWithProfile / $totalStudents * 100) : 0 }}%</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:1.25rem;align-items:start;">

    {{-- Recent Registrations --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Registrations</span>
            <a href="{{ route('admin.students') }}" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Profile</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentUsers as $u)
                <tr class="tr-link" onclick="window.location='{{ route('admin.students.show', $u) }}'">
                    <td style="font-weight:500;">{{ $u->name }}</td>
                    <td style="color:#6b7280;">{{ $u->email }}</td>
                    <td>
                        <span class="badge badge-{{ $u->role }}">{{ ucfirst($u->role) }}</span>
                    </td>
                    <td>
                        @if($u->studentProfile)
                            <span class="badge badge-ok">Yes</span>
                        @else
                            <span style="color:#9ca3af;font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="color:#6b7280;font-size:12.5px;">{{ $u->created_at->format('M j, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:2rem;">No users yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Top Specializations --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Top Specializations</span>
        </div>
        <div class="card-body" style="padding:0.75rem 1.25rem;">
            @forelse($specCounts as $spec => $count)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid #f0ede9;">
                <span style="font-size:13px;color:#374151;">{{ $spec }}</span>
                <span style="font-family:'Oswald',sans-serif;font-size:18px;color:var(--navy);font-weight:600;">{{ $count }}</span>
            </div>
            @empty
            <p style="color:#9ca3af;font-size:13px;text-align:center;padding:1rem 0;">No profiles yet.</p>
            @endforelse
        </div>
    </div>

</div>

{{-- Quick Links --}}
<div class="card" style="margin-top:1.25rem;">
    <div class="card-header"><span class="card-title">Quick Actions</span></div>
    <div class="card-body" style="display:flex;gap:0.75rem;flex-wrap:wrap;">
        <a href="{{ route('admin.students.export') }}" class="btn btn-secondary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export Students CSV
        </a>
        <a href="{{ route('admin.requirements') }}" class="btn btn-secondary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Edit Requirements
        </a>
        <a href="{{ route('admin.system-prompt') }}" class="btn btn-secondary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Edit System Prompt
        </a>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Manage Users
        </a>
        <a href="{{ route('admin.stats') }}" class="btn btn-secondary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            View Statistics
        </a>
    </div>
</div>

@endsection
