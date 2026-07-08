@extends('layouts.account')

@section('title', 'Wallet Transactions')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('wallet.index') }}" class="btn btn-sm btn-outline-secondary me-3">&larr; Back</a>
        <h4 class="mb-0 fw-semibold">Transaction History</h4>
        <span class="ms-auto text-muted small">Balance: <strong>{{ money($wallet->balance) }}</strong></span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($transactions->isEmpty())
                <p class="text-muted text-center py-5 mb-0">No transactions found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Balance After</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $entry)
                                <tr>
                                    <td class="text-muted small">{{ $entry->created_at->format('d M Y, g:ia') }}</td>
                                    <td class="font-monospace small">{{ $entry->reference }}</td>
                                    <td>{{ $entry->description }}</td>
                                    <td>
                                        <span class="badge bg-{{ $entry->isCredit() ? 'success' : 'danger' }}-subtle text-{{ $entry->isCredit() ? 'success' : 'danger' }}">
                                            {{ $entry->type->value }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold {{ $entry->isCredit() ? 'text-success' : 'text-danger' }}">
                                        {{ $entry->isCredit() ? '+' : '-' }}{{ money($entry->amount) }}
                                    </td>
                                    <td class="text-end text-muted">{{ money($entry->balance_after) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
