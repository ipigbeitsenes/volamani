@extends('layouts.admin')

@section('title', 'Affiliates')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Affiliates</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Affiliate Program</h4>
        <a href="{{ route('admin.affiliates.commissions') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-cash-stack me-1"></i>Manage commissions
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Pending commissions</p>
                    <h4 class="fw-bold mb-0">{{ $stats['pending_count'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Pending payout</p>
                    <h4 class="fw-bold mb-0 text-warning">{{ money($stats['pending_payout']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-2">Top affiliates</p>
                    @forelse($stats['top'] as $top)
                        <div class="d-flex justify-content-between small {{ ! $loop->last ? 'mb-1' : '' }}">
                            <span>{{ $top->user->name ?? '—' }}</span>
                            <span class="fw-semibold">{{ money($top->total_earned) }}</span>
                        </div>
                    @empty
                        <span class="text-muted small">No affiliates yet.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Name, email or code">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Enums\AffiliateStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Affiliate</th><th>Code</th><th>Status</th><th>Signups</th><th>Conversions</th><th>Earned</th><th>Paid</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>
                                <a href="{{ route('admin.affiliates.show', $account) }}" class="fw-semibold text-decoration-none">
                                    {{ $account->user->name ?? '—' }}
                                </a>
                                <div class="small text-muted">{{ $account->user->email ?? '' }}</div>
                            </td>
                            <td><code>{{ $account->code() }}</code></td>
                            <td><span class="badge bg-{{ $account->status->badge() }}">{{ $account->status->label() }}</span></td>
                            <td>{{ $account->signups_count }}</td>
                            <td>{{ $account->conversions_count }}</td>
                            <td class="fw-semibold">{{ money($account->total_earned) }}</td>
                            <td class="text-success">{{ money($account->total_paid) }}</td>
                            <td class="text-end">
                                @if($account->status === \App\Enums\AffiliateStatus::Active)
                                    <form action="{{ route('admin.affiliates.suspend', $account) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Suspend</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.affiliates.activate', $account) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">No affiliate accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $accounts->withQueryString()->links() }}</div>
</div>
@endsection
