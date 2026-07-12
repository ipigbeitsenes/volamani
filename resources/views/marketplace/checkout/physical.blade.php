@extends('layouts.app')

@section('title', 'Checkout — ' . $product->name)

@php
    $detail = $product->physicalDetail;
    $vendor = $product->vendor;
    $hasVariants = $product->hasVariants();
@endphp

@section('content')
<div class="container py-4" style="max-width: 920px;">
    <a href="{{ route('marketplace.products.show', $product->slug) }}" class="btn btn-sm btn-link text-decoration-none mb-3">
        <i class="bi bi-arrow-left"></i> Back to product
    </a>

    <h1 class="h4 fw-bold mb-1">Checkout</h1>
    <p class="text-muted">{{ $product->name }} · sold by {{ $vendor->business_name }}</p>

    <form method="POST" action="{{ route('checkout.physical.process', $product) }}">
        @csrf
        <div class="row g-4">
            {{-- Left: options + address --}}
            <div class="col-lg-7">
                {{-- Variant + qty --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Item</div>
                    <div class="card-body">
                        <div class="d-flex gap-3 mb-3">
                            <img src="{{ $product->thumbnail_url }}" class="rounded border bg-light" style="width:72px;height:72px;object-fit:contain;" alt="">
                            <div>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                <div class="small text-muted">{{ $product->displayCategory() }}</div>
                                @if($detail)
                                    <span class="badge bg-{{ $detail->condition->badge() }}">{{ $detail->condition->label() }}</span>
                                @endif
                            </div>
                        </div>

                        @if($hasVariants)
                            <label class="form-label fw-medium small">Choose an option <span class="text-danger">*</span></label>
                            <div class="mb-3">
                                @foreach($product->variants->where('is_active', true) as $variant)
                                    <div class="form-check border rounded p-2 mb-2 {{ $variant->inStock() ? '' : 'opacity-50' }}">
                                        <input class="form-check-input" type="radio" name="variant_id" id="v{{ $variant->id }}"
                                               value="{{ $variant->id }}" {{ old('variant_id') == $variant->id ? 'checked' : '' }}
                                               data-price="{{ $variant->effectivePrice() }}" {{ $variant->inStock() ? '' : 'disabled' }} required>
                                        <label class="form-check-label d-flex justify-content-between w-100" for="v{{ $variant->id }}">
                                            <span>{{ $variant->name }}</span>
                                            <span>
                                                <span class="fw-semibold">{{ money($variant->effectivePrice()) }}</span>
                                                <span class="badge {{ $variant->inStock() ? 'bg-success' : 'bg-secondary' }} ms-1">
                                                    {{ $variant->inStock() ? $variant->stock_quantity . ' left' : 'Out' }}
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('variant_id')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                        @endif

                        <label class="form-label fw-medium small">Quantity</label>
                        <input type="number" name="quantity" min="1" max="999" value="{{ old('quantity', 1) }}"
                               class="form-control @error('quantity') is-invalid @enderror" style="max-width:140px;" id="qtyInput">
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Shipping address --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Delivery Address</div>
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
                                <label class="form-label small">State / Region</label>
                                <input type="text" name="ship_to_state" class="form-control" value="{{ old('ship_to_state') }}" placeholder="State / region">
                            </div>
                        </div>
                        @if($vendor->ships_to)
                            <div class="form-text mt-2"><i class="bi bi-truck me-1"></i>{{ $vendor->ships_to }}</div>
                        @endif
                    </div>
                </div>

                {{-- Payment method --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Payment Method</div>
                    <div class="card-body">
                        @foreach($gateways as $gw)
                            <div class="form-check border rounded p-2 mb-2">
                                <input class="form-check-input" type="radio" name="gateway" id="gw{{ $gw->value }}"
                                       value="{{ $gw->value }}" {{ old('gateway', 'wallet') === $gw->value ? 'checked' : '' }} required>
                                <label class="form-check-label" for="gw{{ $gw->value }}">
                                    <i class="bi {{ $gw->icon() }} me-1"></i>{{ $gw->label() }}
                                </label>
                            </div>
                        @endforeach
                        @error('gateway')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Right: summary --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm position-sticky" style="top:1rem;">
                    <div class="card-header bg-white fw-semibold">Order Summary</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Item price</span>
                            <span id="sumUnit">{{ money($product->lowestPrice()) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping</span>
                            <span>
                                @if($vendor->shipping_fee)
                                    {{ money($vendor->shipping_fee) }}
                                    @if($vendor->free_shipping_threshold)
                                        <span class="d-block small text-success text-end">Free over {{ money($vendor->free_shipping_threshold) }}</span>
                                    @endif
                                @else
                                    <span class="text-success">Free</span>
                                @endif
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                            <span>Estimated total</span>
                            <span id="sumTotal">—</span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-lock-fill me-1"></i>Place Order
                        </button>
                        <div class="form-text text-center mt-2">
                            Funds are held in escrow and released to the seller only after you confirm delivery.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    (function () {
        const base       = {{ $product->lowestPrice() }};
        const shipping   = {{ (int) $vendor->shipping_fee }};
        const freeOver   = {{ $vendor->free_shipping_threshold !== null ? (int) $vendor->free_shipping_threshold : 'null' }};
        const qtyInput   = document.getElementById('qtyInput');
        const sumUnit    = document.getElementById('sumUnit');
        const sumTotal   = document.getElementById('sumTotal');
        const fmtMoney = k => @json(currency_symbol()) + (k / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        function unitPrice() {
            const sel = document.querySelector('input[name=variant_id]:checked');
            return sel ? parseInt(sel.dataset.price, 10) : base;
        }
        function recalc() {
            const qty = Math.max(1, parseInt(qtyInput.value || '1', 10));
            const sub = unitPrice() * qty;
            let ship = shipping;
            if (freeOver !== null && sub >= freeOver) ship = 0;
            sumUnit.textContent = fmtMoney(unitPrice());
            sumTotal.textContent = fmtMoney(sub + ship);
        }
        document.querySelectorAll('input[name=variant_id]').forEach(el => el.addEventListener('change', recalc));
        qtyInput.addEventListener('input', recalc);
        recalc();
    })();
</script>
@endpush
@endsection
