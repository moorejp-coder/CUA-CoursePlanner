@extends('admin.layout')

@section('title', 'Students')
@section('heading', 'Students')

@section('content')

<div class="card">
    <div class="card-header">
        <span class="card-title">All Students</span>
        <div style="display:flex;gap:0.5rem;align-items:center;">
            <form method="GET" action="{{ route('admin.students') }}" style="display:flex;gap:0.5rem;">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email…" class="text-input" style="width:220px;">
                <button type="submit" class="btn btn-secondary btn-sm">Search</button>
                @if($search)
                    <a href="{{ route('admin.students') }}" class="btn btn-secondary btn-sm">Clear</a>
                @endif
            </form>
            <a href="{{ route('admin.students.export') }}" class="btn btn-primary btn-sm">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Degree</th>
                <th>Specialization</th>
                <th>GPA</th>
                <th>Credits</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
            <tr class="tr-link" onclick="window.location='{{ route('admin.students.show', $student) }}'">
                <td style="font-weight:500;">{{ $student->name }}</td>
                <td style="color:#6b7280;font-size:12.5px;">{{ $student->email }}</td>
                <td><span class="badge badge-{{ $student->role }}">{{ ucfirst($student->role) }}</span></td>
                <td style="font-size:12.5px;">{{ $student->studentProfile?->degree ?? '—' }}</td>
                <td style="font-size:12.5px;">{{ $student->studentProfile?->specialization_1 ?? '—' }}</td>
                <td style="font-size:12.5px;">{{ $student->studentProfile?->gpa ?? '—' }}</td>
                <td style="font-size:12.5px;">{{ $student->studentProfile?->credits_completed ?? '—' }}</td>
                <td style="color:#6b7280;font-size:12px;">{{ $student->created_at->format('M j, Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;color:#9ca3af;padding:2rem;">
                    {{ $search ? 'No students found matching "'.$search.'".' : 'No students registered yet.' }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($students->hasPages())
    <div style="padding:0.75rem 1rem;border-top:1px solid var(--border);">
        {{ $students->links() }}
    </div>
    @endif
</div>

@endsection
