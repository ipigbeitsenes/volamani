@extends('layouts.admin')

@section('title', 'Subscriptions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Subscriptions</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Subscriptions</h4>
        <a href="{{ route('admin.subscriptions.plans') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-collection me-1"></i>Manage plans
        </a>
    </div>

    <div class="row g-3 mb-4">
        @php $cards = [
            ['Active', $stats['active'], 'success'],
            ['Past due', $stats['past_due'], 'warning'],
            ['Cancelled', $stats['cancelled'], 'secondary'],
            ['Active MRR', money($stats['mrr']), 'primary'],
        ]; @endphp
        @foreach($cards as [$label, $value, $color])
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">{{ $label }}</p>
                        <h4 class="fw-bold mb-0 text-{{ $color }}">{{ $value }}</h4>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Vendor or reference">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Enums\SubscriptionStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Plan</label>
                    <select name="plan" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string)($filters['plan'] ?? '') === (string)$plan->id)>{{ $plan->name }}</option>
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
                    <tr><th>Reference</th><th>Vendor</th><th>Plan</th><th>Price</th><th>Status</th><th>Renews / Ends</th></tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td><code>{{ $sub->reference }}</code></td>
                            <td>{{ $sub->vendor->business_name ?? '—' }}</td>
                            <td>{{ $sub->plan->name ?? '—' }}</td>
                            <td>{{ money($sub->price) }}</td>
                            <td><span class="badge bg-{{ $sub->status->badge() }}">{{ $sub->status->label() }}</span></td>
                            <td class="small text-muted">{{ $sub->ends_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No subscriptions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $subscriptions->withQueryString()->links() }}</div>
</div>
@endsection
