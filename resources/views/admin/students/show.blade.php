@extends('admin.layout')

@section('title', $user->name)
@section('heading', $user->name)

@section('content')

<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.students') }}" style="color:var(--navy);font-size:13px;text-decoration:none;">← Back to Students</a>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:1.25rem;align-items:start;">

    {{-- User Info --}}
    <div>
        <div class="card">
            <div class="card-header"><span class="card-title">Account</span></div>
            <div class="card-body">
                <table style="width:100%;">
                    <tbody>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;width:90px;">Name</td>
                            <td style="font-size:13px;font-weight:500;">{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Email</td>
                            <td style="font-size:13px;">{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Role</td>
                            <td><span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Verified</td>
                            <td style="font-size:13px;">{{ $user->email_verified_at ? $user->email_verified_at->format('M j, Y') : 'No' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Joined</td>
                            <td style="font-size:13px;">{{ $user->created_at->format('M j, Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($user->studentProfile)
        <div class="card" style="margin-top:1rem;">
            <div class="card-header"><span class="card-title">Academic Profile</span></div>
            <div class="card-body">
                @php $p = $user->studentProfile; @endphp
                <table style="width:100%;">
                    <tbody>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;width:110px;">Degree</td>
                            <td style="font-size:13px;">{{ $p->degree ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Catalog Year</td>
                            <td style="font-size:13px;">{{ $p->catalog_year ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Spec 1</td>
                            <td style="font-size:13px;">{{ $p->specialization_1 ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Spec 2</td>
                            <td style="font-size:13px;">{{ $p->specialization_2 ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">GPA</td>
                            <td style="font-size:13px;">{{ $p->gpa ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Credits</td>
                            <td style="font-size:13px;">{{ $p->credits_completed ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Standing</td>
                            <td style="font-size:13px;">{{ $p->projected_standing ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;color:#6b7280;padding:0.3rem 0;">Semester</td>
                            <td style="font-size:13px;">{{ $p->current_semester ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card" style="margin-top:1rem;">
            <div class="card-body" style="text-align:center;color:#9ca3af;font-size:13px;padding:1.5rem;">
                No academic profile created yet.
            </div>
        </div>
        @endif
    </div>

    {{-- Courses --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Courses ({{ $user->studentCourses->count() }})</span>
        </div>
        @if($user->studentCourses->isEmpty())
            <div style="padding:2rem;text-align:center;color:#9ca3af;font-size:13px;">No courses recorded.</div>
        @else
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Semester</th>
                    <th>Grade</th>
                    <th>Credits</th>
                </tr>
            </thead>
            <tbody>
                @foreach($user->studentCourses->sortBy('requirement_category') as $course)
                <tr>
                    <td style="font-family:monospace;font-size:12.5px;">{{ $course->course_code ?? '—' }}</td>
                    <td style="font-weight:500;font-size:13px;">{{ $course->course_name }}</td>
                    <td style="font-size:12px;color:#6b7280;">{{ str_replace('_', ' ', ucfirst($course->requirement_category)) }}</td>
                    <td>
                        @php
                            $statusColors = ['completed'=>'badge-ok','in_progress'=>'badge-warn','planned'=>'badge-student'];
                            $sc = $statusColors[$course->status] ?? 'badge-student';
                        @endphp
                        <span class="badge {{ $sc }}">{{ ucfirst(str_replace('_', ' ', $course->status)) }}</span>
                    </td>
                    <td style="font-size:12.5px;color:#6b7280;">{{ $course->semester_completed ?? '—' }}</td>
                    <td style="font-size:12.5px;">{{ $course->grade ?? '—' }}</td>
                    <td style="font-size:12.5px;">{{ $course->credits ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>

@endsection
