@extends('layouts.app')

@section('title', 'Security')

@section('content')
<div class="container py-4" style="max-width: 820px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Security</h1>
            <p class="text-muted small mb-0">Recent activity on your account.</p>
        </div>
        <a href="{{ route('profile.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to profile</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Last sign-in</div>
                    <div class="fw-semibold">{{ $user->last_login_at?->format('d M Y, H:i') ?? 'No record' }}</div>
                    @if($user->last_login_ip)<div class="small text-muted">from {{ $user->last_login_ip }}</div>@endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Password</div>
                        <div class="fw-semibold">Manage on profile</div>
                    </div>
                    <a href="{{ route('profile.index') }}#password" class="btn btn-sm btn-outline-primary">Change</a>
                </div>
            </div>
        </div>
    </div>

    @if($user->isLocked())
        <div class="alert alert-warning"><i class="bi bi-lock me-1"></i>Your account is currently locked until {{ $user->locked_until?->format('d M H:i') }} after repeated failed sign-ins.</div>
    @endif

    <div class="card">
        <div class="card-header bg-white fw-semibold">Recent security activity</div>
        <div class="list-group list-group-flush">
            @forelse($logs as $log)
                <div class="list-group-item d-flex gap-3 align-items-start">
                    <span class="text-{{ $log->event->severity() === 'danger' ? 'danger' : ($log->event->severity() === 'warning' ? 'warning' : 'secondary') }}">
                        <i class="bi {{ $log->event->icon() }} fs-5"></i>
                    </span>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $log->event->label() }}</div>
                        <div class="small text-muted">
                            {{ $log->created_at?->format('d M Y, H:i') }}
                            @if($log->ip_address) · {{ $log->ip_address }}@endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center text-muted py-5">No activity recorded yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
