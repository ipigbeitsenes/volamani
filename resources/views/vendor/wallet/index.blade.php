@extends('layouts.vendor')

@section('title', 'Vendor Wallet')

@section('content')
<div class="container-fluid py-4">

    <div class="row g-4 mb-4">
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
                    <h4 class="fw-bold mb-0">{{ money($wallet->balance) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1 small">In Escrow</p>
                    <h4 class="fw-bold mb-0 text-warning">{{ money($wallet->escrow_balance) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Withdrawal Request --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-3">Request Withdrawal</h5>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($wallet->is_frozen)
                        <div class="alert alert-danger">Your wallet is currently frozen. Contact support.</div>
                    @elseif($wallet->availableBalance() < 100000)
                        <div class="alert alert-warning">Minimum withdrawal balance is {{ currency_symbol() }}1,000.</div>
                    @else
                    <form action="{{ route('vendor.wallet.withdraw') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Amount ({{ currency_symbol() }})</label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                   min="1000" step="100" value="{{ old('amount') }}" placeholder="Minimum {{ currency_symbol() }}1,000">
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                   value="{{ old('bank_name') }}" placeholder="e.g. GTBank">
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror"
                                   value="{{ old('account_name') }}">
                            @error('account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror"
                                   value="{{ old('account_number') }}" maxlength="10">
                            @error('account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <p class="text-muted small mb-3">1.5% processing fee applies. Payments within 24 hours.</p>
                        <button type="submit" class="btn btn-primary w-100">Request Withdrawal</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    <span>Recent Transactions</span>
                    <a href="{{ route('vendor.wallet.transactions') }}" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($transactions->isEmpty())
                        <p class="text-center text-muted py-4 mb-0">No transactions yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $entry)
                                        <tr>
                                            <td class="text-muted">{{ $entry->created_at->format('d M, g:ia') }}</td>
                                            <td>{{ Str::limit($entry->description, 40) }}</td>
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

            {{-- Recent Withdrawals --}}
            @if($withdrawals->isNotEmpty())
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white fw-semibold small">Withdrawal Requests</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Bank</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($withdrawals as $w)
                                    <tr>
                                        <td class="text-muted">{{ $w->created_at->format('d M') }}</td>
                                        <td>{{ $w->bank_name }}</td>
                                        <td class="text-end">{{ money($w->net_amount) }}</td>
                                        <td>
                                            @php
                                                $color = match($w->status->value) {
                                                    'pending' => 'warning', 'processing' => 'info',
                                                    'paid' => 'success', default => 'danger'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">
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
            @endif
        </div>

    </div>
</div>
@endsection
