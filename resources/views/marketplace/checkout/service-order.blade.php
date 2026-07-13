@extends('layouts.app')

@section('title', 'Checkout — Service Order')

@section('content')
<div class="container py-5" style="max-width:860px">
    <h4 class="mb-4">Pay for Service Order</h4>

    <div class="row g-4">
        <div class="col-lg-5 order-lg-2">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-body">
                    <h5 class="card-title">Order Summary</h5>
                    <h6>{{ $serviceOrder->service->title }}</h6>
                    <p class="small text-muted mb-1">Package: <strong>{{ $serviceOrder->package->name }}</strong></p>
                    <p class="small text-muted mb-3">Vendor: {{ $serviceOrder->vendor->business_name }}</p>
                    <hr>
                    <dl class="row small mb-2">
                        <dt class="col-7">Subtotal</dt>
                        <dd class="col-5 text-end">{{ money($serviceOrder->total_amount) }}</dd>
                        <dt class="col-7 text-muted">Platform Fee</dt>
                        <dd class="col-5 text-end text-muted">{{ money($serviceOrder->platform_fee) }}</dd>
                    </dl>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span class="text-success">{{ money($serviceOrder->total_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 order-lg-1">
            <form method="POST" action="{{ route('checkout.process') }}">
                @csrf
                <input type="hidden" name="payable_type" value="service_order">
                <input type="hidden" name="payable_id" value="{{ $serviceOrder->id }}">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Choose Payment Method</h5>
                        @foreach ($gateways as $gateway)
                            <div class="form-check border rounded p-3 mb-2">
                                <input class="form-check-input" type="radio" name="gateway"
                                    id="gw_{{ $gateway->value }}" value="{{ $gateway->value }}"
                                    @checked($loop->first)>
                                <label class="form-check-label d-flex align-items-center gap-2" for="gw_{{ $gateway->value }}">
                                    <i class="{{ $gateway->icon() }} fs-4"></i>
                                    <div>
                                        <div class="fw-semibold">{{ $gateway->label() }}</div>
                                        @if ($gateway->value === 'bank_transfer')
                                            <small class="text-muted">Manual verification — 2–4 hrs</small>
                                        @elseif ($gateway->value === 'paystack')
                                            <small class="text-muted">Instant — Card, Bank, USSD</small>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                @feature('escrow')
                <div class="alert alert-info small">
                    <i class="bi bi-shield-lock me-1"></i>
                    Your payment is held in <strong>escrow</strong> and only released to the vendor after you approve delivery.
                </div>
                @endfeature

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    Pay {{ money($serviceOrder->total_amount) }} <i class="bi bi-lock-fill ms-2"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
