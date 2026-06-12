@extends('layouts.app')

@section('title', 'My Wallet')

@section('content')
<div class="container py-4">
    <div class="row g-4">

        {{-- Balance Cards --}}
        <div class="col-12">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-white bg-primary">
                        <div class="card-body">
                            <p class="mb-1 opacity-75 small">Available Balance</p>
                            <h3 class="fw-bold mb-0">{{ money($wallet->availableBalance()) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1 small">Total Balance</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ money($wallet->balance) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1 small">Escrow</p>
                            <h4 class="fw-bold mb-0 text-warning">{{ money($wallet->escrow_balance) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fund Wallet --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-3">Fund Wallet</h5>
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form action="{{ route('wallet.fund') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Amount (₦)</label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                   placeholder="Enter amount" min="500" step="100"
                                   value="{{ old('amount') }}">
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum: ₦500</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Fund via Paystack
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Withdrawal --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-3">Request Withdrawal</h5>
                    @if($wallet->is_frozen)
                        <div class="alert alert-danger">Your wallet is frozen. Contact support.</div>
                    @else
                    <form action="{{ route('wallet.withdraw') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Amount (₦)</label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                   placeholder="Enter amount" min="1000" step="100"
                                   value="{{ old('amount') }}">
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                   value="{{ old('bank_name') }}" placeholder="e.g. Access Bank">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror"
                                   value="{{ old('account_name') }}">
                            @error('account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror"
                                   value="{{ old('account_number') }}" maxlength="10">
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100">
                            Submit Withdrawal
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span>Recent Transactions</span>
                    <a href="{{ route('wallet.transactions') }}" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($transactions->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">No transactions yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
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
                    @endif
                </div>
            </div>
        </div>

        {{-- Withdrawal History --}}
        @if($withdrawals->isNotEmpty())
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Recent Withdrawals</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Bank</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Net</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($withdrawals as $w)
                                    <tr>
                                        <td class="text-muted small">{{ $w->created_at->format('d M Y') }}</td>
                                        <td class="font-monospace small">{{ $w->reference }}</td>
                                        <td>{{ $w->bank_name }}<br><span class="text-muted small">{{ $w->account_number }}</span></td>
                                        <td class="text-end">{{ money($w->amount) }}</td>
                                        <td class="text-end text-success">{{ money($w->net_amount) }}</td>
                                        <td>
                                            @php
                                                $statusColor = match($w->status->value) {
                                                    'pending'    => 'warning',
                                                    'processing' => 'info',
                                                    'paid'       => 'success',
                                                    'rejected','failed' => 'danger',
                                                    default      => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                                {{ ucfirst($w->status->value) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
