@extends('admin.layout')

@section('title', 'Users')
@section('heading', 'User Management')

@section('content')

<div class="card">
    <div class="card-header">
        <span class="card-title">All Users</span>
        <form method="GET" action="{{ route('admin.users') }}" style="display:flex;gap:0.5rem;">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email…" class="text-input" style="width:220px;">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            @if($search)
                <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-sm">Clear</a>
            @endif
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Current Role</th>
                <th>Joined</th>
                <th>Change Role</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr id="user-row-{{ $u->id }}">
                <td style="font-weight:500;">{{ $u->name }}</td>
                <td style="color:#6b7280;font-size:12.5px;">{{ $u->email }}</td>
                <td>
                    <span class="badge badge-{{ $u->role }}" id="badge-{{ $u->id }}">{{ ucfirst($u->role) }}</span>
                </td>
                <td style="color:#6b7280;font-size:12px;">{{ $u->created_at->format('M j, Y') }}</td>
                <td>
                    @if($u->id !== auth()->id())
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                        <select id="role-select-{{ $u->id }}" class="text-input" style="width:110px;padding:4px 8px;font-size:12px;">
                            <option value="student" {{ $u->role === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="dean" {{ $u->role === 'dean' ? 'selected' : '' }}>Dean</option>
                            @if(auth()->user()->isAdmin())
                            <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            @endif
                        </select>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="updateRole({{ $u->id }})">Save</button>
                        <span id="msg-{{ $u->id }}" style="font-size:12px;color:var(--navy);display:none;"></span>
                    </div>
                    @else
                    <span style="font-size:12px;color:#9ca3af;">You</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#9ca3af;padding:2rem;">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div style="padding:0.75rem 1rem;border-top:1px solid var(--border);">
        {{ $users->links() }}
    </div>
    @endif
</div>

<script>
const badgeClasses = { student: 'badge-student', dean: 'badge-dean', admin: 'badge-admin' };

async function updateRole(userId) {
    const select = document.getElementById('role-select-' + userId);
    const msg = document.getElementById('msg-' + userId);
    const badge = document.getElementById('badge-' + userId);
    const role = select.value;

    msg.style.display = 'none';

    try {
        const resp = await fetch(`/admin/users/${userId}/role`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ role }),
        });

        const data = await resp.json();

        if (resp.ok && data.success) {
            badge.className = 'badge ' + (badgeClasses[role] || 'badge-student');
            badge.textContent = role.charAt(0).toUpperCase() + role.slice(1);
            msg.style.color = '#065f46';
            msg.textContent = 'Saved';
        } else {
            msg.style.color = 'var(--red)';
            msg.textContent = data.message || 'Error';
        }
    } catch {
        msg.style.color = 'var(--red)';
        msg.textContent = 'Error';
    }

    msg.style.display = 'inline';
    setTimeout(() => { msg.style.display = 'none'; }, 2500);
}
</script>

@endsection
