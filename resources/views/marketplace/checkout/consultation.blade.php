@extends('layouts.app')

@section('title', 'Checkout — Consultation')

@section('content')
<div class="container py-5" style="max-width:860px">
    <h4 class="mb-4">Pay for Consultation</h4>

    <div class="row g-4">
        <div class="col-lg-5 order-lg-2">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-body">
                    <h5 class="card-title">Session Summary</h5>
                    <h6>{{ $session->package->name }}</h6>
                    <p class="small text-muted mb-1">with <strong>{{ $session->profile->display_name }}</strong></p>
                    <p class="small text-muted mb-1">
                        <i class="bi bi-calendar3 me-1"></i>{{ $session->scheduled_at->format('D, d M Y g:i A') }}
                    </p>
                    <p class="small text-muted mb-3">
                        <i class="bi bi-clock me-1"></i>{{ $session->package->durationLabel() }}
                    </p>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span class="text-success">{{ money($session->price) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 order-lg-1">
            <form method="POST" action="{{ route('checkout.process') }}">
                @csrf
                <input type="hidden" name="payable_type" value="consultation">
                <input type="hidden" name="payable_id" value="{{ $session->id }}">

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

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    Pay {{ money($session->price) }} <i class="bi bi-lock-fill ms-2"></i>
                </button>
                <p class="text-center text-muted small mt-2">
                    <i class="bi bi-shield-check me-1"></i>Payment held until your session is confirmed.
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
