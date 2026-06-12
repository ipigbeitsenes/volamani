@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
@php
    $total      = $summary['total'];
    $available  = $wallet->availableBalance();
    $canWallet  = $available >= $total;
    $singlePayable = $payableCount === 1;
@endphp
<div class="container py-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-lock me-2"></i>Checkout</h4>

    <div class="row g-4">
        <div class="col-lg-7">
            @foreach($summary['groups'] as $group)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white d-flex align-items-center gap-2">
                        <i class="bi bi-shop text-primary"></i>
                        <span class="fw-semibold">{{ $group['vendor']?->business_name ?? 'Volamani' }}</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach($group['lines'] as $line)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $line['name'] }} @if($line['qty'] > 1)<span class="text-muted">× {{ $line['qty'] }}</span>@endif</span>
                                <span class="fw-semibold">{{ money($line['subtotal']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-1">
                        <span>Total</span>
                        <span class="text-primary">{{ money($total) }}</span>
                    </div>
                    <p class="text-muted small mb-4">
                        {{ $payableCount }} order(s) across {{ count($summary['groups']) }} seller(s).
                    </p>

                    {{-- Wallet (pays everything at once) --}}
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold"><i class="bi bi-wallet2 me-1 text-primary"></i>Volamani Wallet</span>
                            <span class="small text-muted">Balance: {{ money($available) }}</span>
                        </div>
                        @if($canWallet)
                            <form method="POST" action="{{ route('cart.process') }}">
                                @csrf
                                <input type="hidden" name="gateway" value="wallet">
                                <button class="btn btn-primary w-100">
                                    <i class="bi bi-lightning-charge me-1"></i>Pay {{ money($total) }} with Wallet
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">Settles every item in one payment.</small>
                        @else
                            <div class="alert alert-warning small mb-2 py-2">
                                Insufficient balance — you need {{ money($total - $available) }} more.
                            </div>
                            <a href="{{ route('wallet.index') }}" class="btn btn-outline-primary btn-sm w-100">Fund Wallet</a>
                        @endif
                    </div>

                    {{-- Card / bank (single seller only) --}}
                    <div class="border rounded p-3">
                        <span class="fw-semibold d-block mb-2"><i class="bi bi-credit-card me-1 text-primary"></i>Card / Bank Transfer</span>
                        @if($singlePayable)
                            <form method="POST" action="{{ route('cart.process') }}" class="mb-2">
                                @csrf
                                <input type="hidden" name="gateway" value="paystack">
                                <button class="btn btn-outline-dark w-100"><i class="bi bi-credit-card me-1"></i>Pay with Card (Paystack)</button>
                            </form>
                            <form method="POST" action="{{ route('cart.process') }}">
                                @csrf
                                <input type="hidden" name="gateway" value="bank_transfer">
                                <button class="btn btn-outline-dark w-100"><i class="bi bi-bank me-1"></i>Pay via Bank Transfer</button>
                            </form>
                        @else
                            <p class="text-muted small mb-0">
                                Your cart has items from multiple sellers. Card/bank checkout covers one seller at a time —
                                use your <strong>wallet</strong> to pay for everything at once, or buy items individually.
                            </p>
                        @endif
                    </div>

                    <a href="{{ route('cart.index') }}" class="btn btn-link w-100 mt-2 text-muted">Back to cart</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
