@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Users</h4>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Name, email or username…">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                @foreach(['admin', 'vendor', 'buyer', 'consultant', 'affiliate'] as $role)
                    <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Any status</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th><th>Roles</th><th>Joined</th><th>Status</th><th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=32&background=1e1b4b&color=fff" class="rounded-circle" width="32" height="32" alt="">
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <div class="small text-muted">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted small">—</span>
                                    @endforelse
                                </td>
                                <td class="small text-muted">{{ $user->created_at->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}-subtle text-{{ $user->is_active ? 'success' : 'secondary' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $users->withQueryString()->links() }}</div>
</div>
@endsection
