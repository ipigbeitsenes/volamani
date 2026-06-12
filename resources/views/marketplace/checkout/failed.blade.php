@extends('layouts.app')

@section('title', 'Payment Failed')

@section('content')
<div class="container py-5 text-center" style="max-width:560px">
    <div class="mb-4">
        <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
            style="width:80px;height:80px">
            <i class="bi bi-x-circle-fill text-danger fs-1"></i>
        </div>
        <h3 class="fw-bold">Payment Failed</h3>
        <p class="text-muted">
            {{ session('error') ?? 'Your payment could not be completed. No money has been deducted.' }}
        </p>
    </div>

    <div class="alert alert-light border mb-4 text-start small">
        <strong>Common reasons:</strong>
        <ul class="mb-0 mt-1">
            <li>Insufficient funds</li>
            <li>Card declined by your bank</li>
            <li>Session expired</li>
            <li>Incorrect card details</li>
        </ul>
    </div>

    <a href="{{ url()->previous() }}" class="btn btn-primary w-100 mb-2">Try Again</a>
    <a href="{{ route('marketplace.index') }}" class="btn btn-outline-secondary w-100">Back to Marketplace</a>

    <p class="text-muted small mt-4">
        Need help? <a href="mailto:support@volamani.com">contact support</a>
    </p>
</div>
@endsection
