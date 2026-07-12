@extends('layouts.app')

@section('title', 'Bank Transfer Payment')

@section('content')
<div class="container py-5" style="max-width:700px">
    <h4 class="mb-1">Bank Transfer</h4>
    <p class="text-muted mb-4">Transfer the exact amount to the account below, then upload your proof.</p>

    {{-- Bank details --}}
    <div class="card border-0 shadow-sm mb-4 bg-primary-subtle">
        <div class="card-body">
            <h5 class="card-title">Transfer To</h5>
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="text-muted small">Bank Name</div>
                    <div class="fw-bold">{{ $bankDetails['bank_name'] }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small">Account Number</div>
                    <div class="fw-bold font-monospace">{{ $bankDetails['account_number'] }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small">Account Name</div>
                    <div class="fw-bold">{{ $bankDetails['account_name'] }}</div>
                </div>
            </div>
            <hr class="my-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Exact Amount to Transfer</div>
                    <div class="h4 mb-0 text-primary fw-bold">{{ money($payment->amount) }}</div>
                </div>
                <div class="text-muted small">
                    Ref: <strong>{{ $payment->reference }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Proof already submitted? --}}
    @if ($proof)
        <div class="alert alert-{{ $proof->status->badge() }} mb-4">
            <strong>{{ $proof->status->label() }}</strong>
            @if ($proof->status->value === 'pending')
                — We'll verify your transfer within 2–4 hours.
            @elseif ($proof->status->value === 'rejected')
                — {{ $proof->rejection_reason }}. Please re-upload.
            @endif
        </div>
    @endif

    @if (!$proof || $proof->status->value === 'rejected')
        {{-- Upload form --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Upload Payment Proof</h5>
                <form method="POST" action="{{ route('checkout.bank-transfer.proof', $payment) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Your Bank Name <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                                class="form-control @error('bank_name') is-invalid @enderror"
                                placeholder="e.g. GTBank" required>
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" value="{{ old('account_name') }}"
                                class="form-control @error('account_name') is-invalid @enderror"
                                placeholder="Name on your account" required>
                            @error('account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount Transferred ({{ currency_symbol() }}) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" value="{{ old('amount', from_kobo($payment->amount)) }}"
                                class="form-control @error('amount') is-invalid @enderror"
                                step="0.01" min="1" required>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" value="{{ old('transfer_date', now()->format('Y-m-d')) }}"
                                class="form-control @error('transfer_date') is-invalid @enderror"
                                max="{{ now()->format('Y-m-d') }}" required>
                            @error('transfer_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Proof of Transfer (screenshot/receipt)</label>
                            <input type="file" name="proof_file"
                                class="form-control @error('proof_file') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">JPG, PNG or PDF. Max 5MB.</div>
                            @error('proof_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes (optional)</label>
                            <input type="text" name="notes" value="{{ old('notes') }}"
                                class="form-control" placeholder="Transaction ID, teller number, etc.">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-1"></i> Submit Payment Proof
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
