@extends('layouts.app')

@section('title', 'Payment Pending Verification')

@section('content')
<div class="container py-5 text-center" style="max-width:560px">
    <div class="mb-4">
        <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
            style="width:80px;height:80px">
            <i class="bi bi-hourglass-split text-warning fs-1"></i>
        </div>
        <h3 class="fw-bold">Payment Under Review</h3>
        <p class="text-muted">
            We've received your payment proof and are verifying your transfer.
            This usually takes <strong>2–4 hours</strong> during business hours.
        </p>
    </div>

    <div class="card border-0 shadow-sm mb-4 text-start">
        <div class="card-body">
            <dl class="row mb-0 small">
                <dt class="col-6">Payment Reference</dt>
                <dd class="col-6 font-monospace">{{ $payment->reference }}</dd>
                <dt class="col-6">Amount</dt>
                <dd class="col-6 fw-bold">{{ money($payment->amount) }}</dd>
                <dt class="col-6">Method</dt>
                <dd class="col-6">Bank Transfer</dd>
                <dt class="col-6">Status</dt>
                <dd class="col-6">
                    <span class="badge bg-warning text-dark">Awaiting Verification</span>
                </dd>
            </dl>
        </div>
    </div>

    <p class="text-muted small mb-4">
        Once approved, your order will be automatically activated. You'll receive an email notification.
        If you haven't uploaded your proof yet, you can do so below.
    </p>

    <a href="{{ route('checkout.bank-transfer', $payment) }}" class="btn btn-outline-primary w-100 mb-2">
        <i class="bi bi-upload me-1"></i> View / Update Proof
    </a>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary w-100">
        Back to Dashboard
    </a>
</div>
@endsection
