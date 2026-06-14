@extends('layouts.finance')

@section('title', 'Escrow')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-1">Escrow Management</h4>
    <p class="text-muted small mb-4">Funds held against orders. Release to the vendor or refund to the buyer.</p>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search reference...">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(\App\Enums\EscrowStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected(($filters['status'] ?? '') === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($escrows->isEmpty())
                <p class="text-muted text-center py-5 mb-0">No escrow records found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Buyer</th>
                                <th>Vendor</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Held</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($escrows as $escrow)
                                <tr>
                                    <td class="font-monospace small">{{ $escrow->reference }}</td>
                                    <td>{{ $escrow->buyer->name ?? '—' }}</td>
                                    <td>{{ $escrow->vendor->business_name ?? '—' }}</td>
                                    <td class="text-end">{{ money($escrow->total_amount) }}</td>
                                    <td class="text-end text-warning">{{ money($escrow->heldAmount()) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $escrow->status->badge() }}-subtle text-{{ $escrow->status->badge() }}">
                                            {{ $escrow->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($escrow->canRelease())
                                            <form method="POST" action="{{ route('finance.escrows.release', $escrow) }}" class="d-inline" onsubmit="return confirm('Release held funds to the vendor?');">@csrf<button class="btn btn-sm btn-success">Release</button></form>
                                        @endif
                                        @if($escrow->canRefund())
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#eref-{{ $escrow->id }}">Refund</button>
                                        @endif
                                        @if(! $escrow->canRelease() && ! $escrow->canRefund())
                                            <span class="small text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($escrow->canRefund())
                                <tr class="collapse" id="eref-{{ $escrow->id }}">
                                    <td colspan="7" class="bg-light">
                                        <form method="POST" action="{{ route('finance.escrows.refund', $escrow) }}" class="row g-2 align-items-end" onsubmit="return confirm('Refund held funds to the buyer wallet?');">
                                            @csrf
                                            <div class="col-md-9"><input name="reason" class="form-control form-control-sm" maxlength="1000" placeholder="Refund reason (optional)"></div>
                                            <div class="col-md-3"><button class="btn btn-sm btn-danger w-100">Confirm refund</button></div>
                                        </form>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">{{ $escrows->withQueryString()->links() }}</div>
</div>
@endsection
