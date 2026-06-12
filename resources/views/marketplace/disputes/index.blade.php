@extends('layouts.app')

@section('title', 'My Support Tickets')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-1">Support Tickets</h4>
    <p class="text-muted mb-4">Issues raised on your purchases, held in escrow until resolved.</p>

    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($disputes->isEmpty())
                <p class="text-muted text-center py-5 mb-0">You have no support tickets.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Order</th>
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
                                    <td class="text-muted small">{{ $dispute->escrow?->reference ?? '—' }}</td>
                                    <td>{{ $dispute->reason->label() }}</td>
                                    <td class="text-end">{{ money($dispute->escrow?->total_amount ?? 0) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $dispute->status->badge() }}-subtle text-{{ $dispute->status->badge() }}">
                                            {{ $dispute->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('disputes.show', $dispute) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">{{ $disputes->links() }}</div>
</div>
@endsection
