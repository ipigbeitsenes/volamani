@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="container py-5 text-center" style="max-width:560px">
    <div class="mb-4">
        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
            style="width:80px;height:80px">
            <i class="bi bi-check-circle-fill text-success fs-1"></i>
        </div>
        <h3 class="fw-bold">Payment Successful!</h3>
        <p class="text-muted">Your payment has been confirmed and your order is being processed.</p>
    </div>

    @if ($payment)
        <div class="card border-0 shadow-sm mb-4 text-start">
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6">Reference</dt>
                    <dd class="col-6 font-monospace">{{ $payment->reference }}</dd>
                    <dt class="col-6">Amount Paid</dt>
                    <dd class="col-6 fw-bold text-success">{{ money($payment->amount) }}</dd>
                    <dt class="col-6">Gateway</dt>
                    <dd class="col-6">{{ $payment->gateway->label() }}</dd>
                    <dt class="col-6">Date</dt>
                    <dd class="col-6">{{ $payment->paid_at?->format('d M Y, g:i A') }}</dd>
                </dl>
            </div>
        </div>

        @php $payable = $payment->payable; @endphp
        @if ($payable instanceof \App\Models\Order)
            @if ($payable->requires_shipping)
                <a href="{{ route('orders.show', $payable) }}" class="btn btn-primary mb-2 w-100">
                    <i class="bi bi-box-seam me-1"></i> View My Order &amp; Track Delivery
                </a>
            @else
                <a href="{{ route('orders.show', $payable) }}" class="btn btn-primary mb-2 w-100">
                    <i class="bi bi-download me-1"></i> Go to My Order (Download)
                </a>
            @endif
        @elseif ($payable instanceof \App\Models\ServiceOrder)
            <a href="{{ route('service-orders.show', $payable) }}" class="btn btn-primary mb-2 w-100">
                <i class="bi bi-briefcase me-1"></i> View Service Order
            </a>
        @elseif ($payable instanceof \App\Models\ConsultationSession)
            <a href="{{ route('consultations.sessions.show', $payable) }}" class="btn btn-primary mb-2 w-100">
                <i class="bi bi-calendar-check me-1"></i> View Consultation Session
            </a>
        @endif
    @endif

    <a href="{{ route('marketplace.index') }}" class="btn btn-outline-secondary w-100">
        Back to Marketplace
    </a>
</div>
@endsection
