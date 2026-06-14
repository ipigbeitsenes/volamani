@extends('layouts.admin')

@section('title', 'Returns')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Returns</li>
@endsection

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Returns &amp; Refunds</h4>
    <p class="text-muted mb-0 small">Oversight of all physical-order returns. You can override a seller's decision.</p>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->value }}" {{ ($filters['status'] ?? '') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Search reference</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="RET-...">
            </div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($returns->isEmpty())
            <div class="text-center text-muted py-5"><i class="bi bi-arrow-return-left fs-2 d-block mb-2"></i>No returns found.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Return</th>
                            <th class="small">Buyer / Seller</th>
                            <th class="small">Reason</th>
                            <th class="small">Status</th>
                            <th class="small text-end">Override</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $r)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $r->reference }}</div>
                                <div class="small text-muted">{{ $r->order->reference ?? '—' }} · {{ $r->created_at->diffForHumans() }}</div>
                                @foreach($r->photoUrls() as $url)
                                    <a href="{{ $url }}" target="_blank" class="badge bg-light text-dark border"><i class="bi bi-image"></i> photo</a>
                                @endforeach
                            </td>
                            <td class="small">{{ $r->buyer->name ?? '—' }}<br><span class="text-muted">{{ $r->vendor->business_name ?? '—' }}</span></td>
                            <td class="small">{{ $r->reason->label() }}</td>
                            <td><span class="badge bg-{{ $r->status->badge() }}">{{ $r->status->label() }}</span></td>
                            <td class="text-end">
                                @if($r->canApprove())
                                    <form method="POST" action="{{ route('admin.returns.approve', $r) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#arej-{{ $r->id }}">Reject</button>
                                @elseif($r->canConfirmReceived())
                                    <form method="POST" action="{{ route('admin.returns.confirm', $r) }}" class="d-inline" onsubmit="return confirm('Refund the buyer and restock?');">@csrf<button class="btn btn-sm btn-primary">Confirm &amp; refund</button></form>
                                @else
                                    <span class="small text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @if($r->canReject())
                        <tr class="collapse" id="arej-{{ $r->id }}">
                            <td colspan="5" class="bg-light">
                                <form method="POST" action="{{ route('admin.returns.reject', $r) }}" class="row g-2 align-items-end">
                                    @csrf
                                    <div class="col-md-9"><input name="decision_note" class="form-control form-control-sm" maxlength="500" required placeholder="Reason for rejection"></div>
                                    <div class="col-md-3"><button class="btn btn-sm btn-danger w-100">Confirm rejection</button></div>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $returns->links() }}</div>
        @endif
    </div>
</div>
@endsection
