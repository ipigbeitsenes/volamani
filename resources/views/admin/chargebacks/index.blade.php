@extends('layouts.admin')

@section('title', 'Chargebacks')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Chargebacks</li>
@endsection

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Chargebacks</h4>
    <p class="text-muted mb-0 small">Payment-gateway disputes. Funds are frozen or clawed back automatically; you settle the final outcome.</p>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(\App\Enums\ChargebackStatus::cases() as $s)
                        <option value="{{ $s->value }}" {{ ($filters['status'] ?? '') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Search reference</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="CBK-...">
            </div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($chargebacks->isEmpty())
            <div class="text-center text-muted py-5"><i class="bi bi-shield-exclamation fs-2 d-block mb-2"></i>No chargebacks found.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Chargeback</th>
                            <th class="small">Vendor</th>
                            <th class="small">Amount</th>
                            <th class="small">Recovered</th>
                            <th class="small">Status</th>
                            <th class="small text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chargebacks as $c)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $c->reference }}</div>
                                <div class="small text-muted">{{ $c->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="small">{{ $c->vendor->business_name ?? '—' }}</td>
                            <td class="small">{{ money($c->amount) }}</td>
                            <td class="small">{{ money($c->clawed_back_amount) }}@if($c->unrecovered_amount > 0)<span class="text-danger"> (−{{ money($c->unrecovered_amount) }})</span>@endif</td>
                            <td><span class="badge bg-{{ $c->status->badge() }}">{{ $c->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.chargebacks.show', $c) }}" class="btn btn-sm btn-outline-primary">Manage</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $chargebacks->links() }}</div>
        @endif
    </div>
</div>
@endsection
