@extends('layouts.admin')

@section('title', 'Escrow Management')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4">Escrow Management</h4>

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
                                <th></th>
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
                                        <a href="{{ route('admin.escrows.show', $escrow) }}" class="btn btn-sm btn-outline-secondary">Manage</a>
                                    </td>
                                </tr>
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
