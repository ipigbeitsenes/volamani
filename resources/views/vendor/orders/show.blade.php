@extends('layouts.vendor')

@section('title', 'Order ' . $order->reference)

@section('content')
<div class="container-fluid py-4" style="max-width: 820px;">
    <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-link text-decoration-none mb-3"><i class="bi bi-arrow-left"></i> Back to orders</a>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="fw-bold mb-1">{{ $order->reference }}</h4>
                <div class="small text-muted">{{ $order->created_at->format('d M Y') }} · Buyer: {{ $order->buyer->name ?? '—' }}</div>
            </div>
            <span class="badge bg-{{ $order->status->badge() }}-subtle text-{{ $order->status->badge() }} fs-6">{{ $order->status->label() }}</span>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Items</div>
        <ul class="list-group list-group-flush">
            @foreach($order->items as $item)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $item->name }} <span class="text-muted small">× {{ $item->quantity }}</span></span>
                    <span class="fw-semibold">{{ money($item->subtotal) }}</span>
                </li>
            @endforeach
        </ul>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between small"><span class="text-muted">Order total</span><span>{{ money($order->total_amount) }}</span></div>
            <div class="d-flex justify-content-between small"><span class="text-muted">Platform fee</span><span>−{{ money($order->platform_fee) }}</span></div>
            <div class="d-flex justify-content-between fw-bold"><span>Your earnings</span><span>{{ money($order->vendor_earnings) }}</span></div>
        </div>
    </div>

    @if($order->requires_shipping)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Ship To</div>
            <div class="card-body">
                @foreach($order->shippingAddressLines() as $line)
                    <div>{{ $line }}</div>
                @endforeach
                <div class="d-flex justify-content-between small mt-2 pt-2 border-top">
                    <span class="text-muted">Shipping fee (included in your earnings)</span>
                    <span>{{ $order->shipping_fee ? money($order->shipping_fee) : 'Free' }}</span>
                </div>
            </div>
        </div>
    @endif

    @if($order->notes)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Notes</div>
            <div class="card-body small" style="white-space: pre-line;">{{ $order->notes }}</div>
        </div>
    @endif

    @if($order->status === \App\Enums\OrderStatus::Cancelled)
        <div class="alert alert-secondary">
            <div class="fw-semibold"><i class="bi bi-x-circle me-1"></i>Order cancelled{{ $order->cancelled_at ? ' on ' . $order->cancelled_at->format('d M Y') : '' }}</div>
            @if($order->cancellation_reason)<div class="small mt-1">Reason: {{ $order->cancellation_reason }}</div>@endif
            <div class="small text-muted mt-1">@feature('wallet')The buyer was refunded to their wallet.@else Any payment the buyer made was refunded.@endfeature</div>
        </div>
    @endif

    @if($order->isPaid() && ! in_array($order->status, [\App\Enums\OrderStatus::Completed, \App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Refunded], true))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Fulfilment</div>
            <div class="card-body">
                @if($order->requires_shipping)
                    {{-- Physical: ship (with tracking) then mark delivered. Buyer confirms to release escrow. --}}
                    @if($order->canShip())
                        <form method="POST" action="{{ route('vendor.orders.ship', $order) }}" class="mb-3">
                            @csrf
                            <label class="form-label small fw-semibold">Mark as shipped</label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" name="tracking_number" class="form-control form-control-sm" placeholder="Tracking number (optional)">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="courier" class="form-control form-control-sm" placeholder="Courier (optional)">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary btn-sm w-100"><i class="bi bi-box-seam me-1"></i>Ship</button>
                                </div>
                            </div>
                        </form>
                    @endif
                    @if($order->canMarkDelivered())
                        <form method="POST" action="{{ route('vendor.orders.deliver', $order) }}">
                            @csrf
                            <button class="btn btn-success btn-sm"><i class="bi bi-truck me-1"></i>Mark as delivered</button>
                            <span class="small text-muted ms-2">The buyer confirms receipt to release your payment.</span>
                        </form>
                    @elseif($order->status === \App\Enums\OrderStatus::Delivered)
                        <div class="text-success small"><i class="bi bi-check-circle me-1"></i>Delivered — awaiting buyer confirmation (or auto-release).</div>
                    @endif
                @else
                    {{-- Digital / custom work --}}
                    <div class="d-flex flex-wrap gap-3 align-items-start">
                        @if($order->status !== \App\Enums\OrderStatus::Delivered)
                            <form method="POST" action="{{ route('vendor.orders.deliver', $order) }}">
                                @csrf
                                <button class="btn btn-success btn-sm"><i class="bi bi-truck me-1"></i>Mark as delivered</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('vendor.orders.upload', $order) }}" enctype="multipart/form-data" class="flex-grow-1">
                            @csrf
                            <label class="form-label small fw-semibold">Upload a deliverable for the buyer</label>
                            <div class="input-group">
                                <input type="file" name="file" class="form-control" required>
                                <button class="btn btn-outline-primary">Upload</button>
                            </div>
                            @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Seller cancellation: refunds the buyer when delivery isn't possible --}}
    @if($order->canVendorCancel())
        <div class="card border-0 shadow-sm mt-3 border-danger-subtle">
            <div class="card-header bg-white fw-semibold text-danger"><i class="bi bi-x-octagon me-1"></i>Can't fulfil this order?</div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    If you're unable to deliver — wrong or undeliverable address, out of stock, or a technical issue —
                    you can cancel this order. <strong>The buyer is fully refunded@feature('wallet') to their wallet@endfeature</strong> and the
                    item is restocked. This can't be undone.
                </p>
                <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#cancelForm">
                    <i class="bi bi-x-circle me-1"></i>Cancel &amp; refund order
                </button>
                <div class="collapse mt-3" id="cancelForm">
                    <form method="POST" action="{{ route('vendor.orders.cancel', $order) }}"
                          onsubmit="return confirm('Cancel this order and refund the buyer? This cannot be undone.');">
                        @csrf
                        <label class="form-label small fw-semibold">Reason for cancellation <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" rows="3" minlength="5" maxlength="500" required
                                  class="form-control form-control-sm @error('cancellation_reason') is-invalid @enderror"
                                  placeholder="e.g. We don't deliver to this location, or the item is no longer available.">{{ old('cancellation_reason') }}</textarea>
                        @error('cancellation_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Shared with the buyer in their cancellation notice.</div>
                        <button class="btn btn-danger btn-sm mt-2"><i class="bi bi-check-lg me-1"></i>Confirm cancellation &amp; refund</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
