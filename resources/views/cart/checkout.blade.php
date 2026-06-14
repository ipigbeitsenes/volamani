@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
@php
    $available     = $wallet->availableBalance();
    $canWallet     = $available >= $grandTotal;
    $singlePayable = $payableCount === 1;
@endphp
<div class="container py-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-lock me-2"></i>Checkout</h4>

    <form method="POST" action="{{ route('cart.process') }}">
        @csrf
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
                                    <span>
                                        {{ $line['name'] }} @if($line['qty'] > 1)<span class="text-muted">× {{ $line['qty'] }}</span>@endif
                                        @if($line['kind'] === 'physical')<span class="badge bg-warning-subtle text-warning ms-1">Physical</span>@endif
                                    </span>
                                    <span class="fw-semibold">{{ money($line['subtotal']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                {{-- Delivery address (only when the cart has physical items) --}}
                @if($hasPhysical)
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-semibold"><i class="bi bi-truck me-1"></i>Delivery Address</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="ship_to_name" class="form-control @error('ship_to_name') is-invalid @enderror"
                                           value="{{ old('ship_to_name', auth()->user()->name) }}" required>
                                    @error('ship_to_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Phone <span class="text-danger">*</span></label>
                                    <input type="tel" name="ship_to_phone" class="form-control @error('ship_to_phone') is-invalid @enderror"
                                           value="{{ old('ship_to_phone', auth()->user()->phone ?? auth()->user()->whatsapp) }}" required>
                                    @error('ship_to_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="ship_to_address" class="form-control @error('ship_to_address') is-invalid @enderror"
                                           value="{{ old('ship_to_address') }}" placeholder="Street, area, landmark" required>
                                    @error('ship_to_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">City</label>
                                    <input type="text" name="ship_to_city" class="form-control" value="{{ old('ship_to_city') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">State</label>
                                    <select name="ship_to_state" class="form-select">
                                        <option value="">Select state</option>
                                        @foreach(['Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','FCT','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara'] as $st)
                                            <option value="{{ $st }}" {{ old('ship_to_state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-text mt-2">One address is used for all physical items. Shipping is a flat fee per seller.</div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Subtotal</span>
                            <span>{{ money($summary['total']) }}</span>
                        </div>
                        @if($shipping > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Shipping</span>
                                <span>{{ money($shipping) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-1">
                            <span>Total</span>
                            <span class="text-primary">{{ money($grandTotal) }}</span>
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
                                <button name="gateway" value="wallet" class="btn btn-primary w-100">
                                    <i class="bi bi-lightning-charge me-1"></i>Pay {{ money($grandTotal) }} with Wallet
                                </button>
                                <small class="text-muted d-block mt-2">Settles every item in one payment.</small>
                            @else
                                <div class="alert alert-warning small mb-2 py-2">
                                    Insufficient balance — you need {{ money($grandTotal - $available) }} more.
                                </div>
                                <a href="{{ route('wallet.index') }}" class="btn btn-outline-primary btn-sm w-100">Fund Wallet</a>
                            @endif
                        </div>

                        {{-- Card / bank (single seller only) --}}
                        <div class="border rounded p-3">
                            <span class="fw-semibold d-block mb-2"><i class="bi bi-credit-card me-1 text-primary"></i>Card / Bank Transfer</span>
                            @if($singlePayable)
                                <button name="gateway" value="paystack" class="btn btn-outline-dark w-100 mb-2"><i class="bi bi-credit-card me-1"></i>Pay with Card (Paystack)</button>
                                <button name="gateway" value="bank_transfer" class="btn btn-outline-dark w-100"><i class="bi bi-bank me-1"></i>Pay via Bank Transfer</button>
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
    </form>
</div>
@endsection
