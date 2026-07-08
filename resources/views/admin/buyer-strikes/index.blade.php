@extends('layouts.admin')

@section('title', 'Buyer Standing')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Buyer Standing</li>
@endsection

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Buyer Standing</h4>
    <p class="text-muted mb-0 small">Buyers who have accrued abuse strikes for unupheld disputes or overturned chargebacks. Flagged accounts are watched; suspended accounts can't purchase or open disputes.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.buyers.index', ['filter' => 'flagged']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Flagged for review</div>
                    <div class="fs-3 fw-bold text-warning">{{ number_format($stats['flagged']) }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.buyers.index', ['filter' => 'suspended']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Suspended</div>
                    <div class="fs-3 fw-bold text-danger">{{ number_format($stats['suspended']) }}</div>
                </div>
            </div>
        </a>
    </div>
</div>

@if($filter)
    <div class="mb-3">
        <span class="badge bg-secondary">Filter: {{ ucfirst($filter) }}</span>
        <a href="{{ route('admin.buyers.index') }}" class="small ms-2">Clear</a>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($buyers->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-emoji-smile fs-2 d-block mb-2"></i>No buyers with strikes{{ $filter ? ' in this view' : '' }}.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Buyer</th>
                            <th class="text-center">Active strikes</th>
                            <th class="text-center">Total</th>
                            <th>Standing</th>
                            <th>Last strike</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($buyers as $buyer)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $buyer->name }}</div>
                                    <div class="text-muted small">{{ $buyer->email }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $buyer->active_strikes_count > 0 ? 'danger' : 'secondary' }}">{{ $buyer->active_strikes_count }}</span>
                                </td>
                                <td class="text-center text-muted">{{ $buyer->buyer_strikes }}</td>
                                <td>
                                    @if($buyer->purchases_suspended)
                                        <span class="badge bg-danger"><i class="bi bi-slash-circle me-1"></i>Suspended</span>
                                    @elseif($buyer->buyer_flagged)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-flag me-1"></i>Flagged</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">OK</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ optional($buyer->buyer_strikes_updated_at)->diffForHumans() ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.buyers.show', $buyer) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="mt-3">{{ $buyers->links() }}</div>
@endsection
