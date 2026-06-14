@extends('layouts.support')

@section('title', 'Support Tickets')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Support Tickets</h4>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search reference...">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(\App\Enums\DisputeStatus::cases() as $case)
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
            @if($disputes->isEmpty())
                <p class="text-muted text-center py-5 mb-0">No tickets found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Buyer</th>
                                <th>Vendor</th>
                                <th>Reason</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($disputes as $dispute)
                                <tr>
                                    <td class="font-monospace small">{{ $dispute->reference }}</td>
                                    <td>{{ $dispute->buyer->name ?? '—' }}</td>
                                    <td>{{ $dispute->vendor->business_name ?? '—' }}</td>
                                    <td class="small">{{ $dispute->reason->label() }}</td>
                                    <td class="text-end">{{ money($dispute->escrow?->total_amount ?? 0) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $dispute->status->badge() }}-subtle text-{{ $dispute->status->badge() }}">
                                            {{ $dispute->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('support.disputes.show', $dispute) }}" class="btn btn-sm btn-outline-secondary">Manage</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">{{ $disputes->withQueryString()->links() }}</div>
</div>
@endsection
