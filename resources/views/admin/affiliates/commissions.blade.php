@extends('layouts.admin')

@section('title', 'Affiliate Commissions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.affiliates.index') }}">Affiliates</a></li>
    <li class="breadcrumb-item active">Commissions</li>
@endsection

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Commissions</h4>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Enums\CommissionStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Enums\CommissionType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
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
                    <tr><th>Reference</th><th>Affiliate</th><th>Type</th><th>Buyer</th><th>Amount</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($commissions as $c)
                        <tr>
                            <td><code>{{ $c->reference }}</code></td>
                            <td>{{ $c->account->user->name ?? '—' }}</td>
                            <td><i class="bi {{ $c->type->icon() }} me-1"></i>{{ $c->type->label() }}</td>
                            <td>{{ $c->buyer->name ?? '—' }}</td>
                            <td class="fw-semibold">{{ money($c->amount) }}</td>
                            <td><span class="badge bg-{{ $c->status->badge() }}">{{ $c->status->label() }}</span></td>
                            <td class="text-end">
                                @if($c->canBeApproved())
                                    <form action="{{ route('admin.affiliates.commissions.approve', $c) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Approve &amp; pay</button>
                                    </form>
                                    <form action="{{ route('admin.affiliates.commissions.cancel', $c) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No commissions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $commissions->withQueryString()->links() }}</div>
</div>
@endsection
