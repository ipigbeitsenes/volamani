@extends('layouts.vendor')

@section('title', 'Escrow & Pending Earnings')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4">Escrow &amp; Pending Earnings</h4>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white bg-warning">
                <div class="card-body">
                    <p class="mb-1 opacity-75 small">Currently Held in Escrow</p>
                    <h3 class="fw-bold mb-0">{{ money($heldTotal) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Escrow Records</div>
        <div class="card-body p-0">
            @if($escrows->isEmpty())
                <p class="text-muted text-center py-5 mb-0">No escrow records yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Buyer</th>
                                <th class="text-end">Your Earnings</th>
                                <th class="text-end">Held</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($escrows as $escrow)
                                <tr>
                                    <td class="font-monospace small">{{ $escrow->reference }}</td>
                                    <td>{{ $escrow->buyer->name ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ money($escrow->vendor_earnings) }}</td>
                                    <td class="text-end text-warning">{{ money($escrow->heldAmount()) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $escrow->status->badge() }}-subtle text-{{ $escrow->status->badge() }}">
                                            {{ $escrow->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('vendor.escrows.show', $escrow) }}" class="btn btn-sm btn-outline-secondary">View</a>
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
