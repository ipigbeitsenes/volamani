@extends('layouts.account')

@section('title', 'My Escrow')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Escrow Protection</h4>
            <p class="text-muted mb-0">Funds held safely until you confirm you've received your order.</p>
        </div>
    </div>

    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($escrows->isEmpty())
                <p class="text-muted text-center py-5 mb-0">You have no escrow-protected payments yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Seller</th>
                                <th>For</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($escrows as $escrow)
                                <tr>
                                    <td class="font-monospace small">{{ $escrow->reference }}</td>
                                    <td>{{ $escrow->vendor->business_name ?? '—' }}</td>
                                    <td class="text-muted small">{{ $escrow->escrowable?->reference ?? class_basename($escrow->escrowable_type) }}</td>
                                    <td class="text-end fw-semibold">{{ money($escrow->total_amount) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $escrow->status->badge() }}-subtle text-{{ $escrow->status->badge() }}">
                                            {{ $escrow->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('escrows.show', $escrow) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">{{ $escrows->links() }}</div>
</div>
@endsection
