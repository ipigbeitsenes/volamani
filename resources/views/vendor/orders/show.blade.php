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

    @if($order->notes)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Notes</div>
            <div class="card-body small" style="white-space: pre-line;">{{ $order->notes }}</div>
        </div>
    @endif

    @if($order->isPaid() && ! in_array($order->status, [\App\Enums\OrderStatus::Completed, \App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Refunded], true))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Fulfilment</div>
            <div class="card-body">
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
            </div>
        </div>
    @endif
</div>
@endsection
