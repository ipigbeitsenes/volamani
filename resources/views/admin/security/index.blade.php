@extends('layouts.admin')

@section('title', 'Security')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Security Center</h4>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['Sign-ins (24h)', $stats['logins_24h'], 'bi-box-arrow-in-right', 'success'],
                ['Failed sign-ins (24h)', $stats['failed_24h'], 'bi-x-circle', 'danger'],
                ['Locked accounts', $stats['locked'], 'bi-lock', 'warning'],
                ['Events today', $stats['events_today'], 'bi-activity', 'primary'],
            ];
        @endphp
        @foreach($cards as [$label, $value, $icon, $color])
            <div class="col-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-{{ $color }} bg-opacity-10 text-{{ $color }}"
                              style="width:48px;height:48px;font-size:1.3rem;"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="h5 fw-bold mb-0">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        {{-- Event feed --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <form method="GET" class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="IP, user name or email…">
                        </div>
                        <div class="col-md-4">
                            <select name="event" class="form-select form-select-sm">
                                <option value="">All events</option>
                                @foreach(\App\Enums\SecurityEvent::cases() as $e)
                                    <option value="{{ $e->value }}" @selected(($filters['event'] ?? '') === $e->value)>{{ $e->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3"><button class="btn btn-sm btn-primary w-100">Filter</button></div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">Event</th><th>User</th><th>IP</th><th>When</th></tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="ps-3">
                                        <span class="badge bg-{{ $log->event->badge() }}-subtle text-{{ $log->event->badge() }}">
                                            <i class="bi {{ $log->event->icon() }} me-1"></i>{{ $log->event->label() }}
                                        </span>
                                        @if($log->description)<div class="small text-muted mt-1">{{ $log->description }}</div>@endif
                                    </td>
                                    <td class="small">
                                        @if($log->user)
                                            <a href="{{ route('admin.users.show', $log->user) }}" class="text-decoration-none">{{ $log->user->name }}</a>
                                        @else
                                            <span class="text-muted">{{ $log->metadata['email'] ?? 'Unknown' }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $log->ip_address ?? '—' }}</td>
                                    <td class="small text-muted text-nowrap">{{ $log->created_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-5">No security events recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $logs->withQueryString()->links() }}</div>
        </div>

        {{-- Locked accounts --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Locked accounts</div>
                <div class="list-group list-group-flush">
                    @forelse($locked as $user)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="small">
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <div class="text-muted">{{ $user->email }}</div>
                                    <div class="text-danger">Locked until {{ $user->locked_until?->format('d M H:i') }}</div>
                                </div>
                                <form method="POST" action="{{ route('admin.security.unlock', $user) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Unlock</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted text-center py-4 small">No locked accounts.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
