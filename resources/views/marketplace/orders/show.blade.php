@extends('layouts.app')

@section('title', 'Order ' . $order->reference)

@section('content')
<div class="container py-4" style="max-width: 820px;">
    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-link text-decoration-none mb-3"><i class="bi bi-arrow-left"></i> Back to orders</a>

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="h5 fw-bold mb-1">{{ $order->reference }}</h1>
                <div class="small text-muted">
                    Placed {{ $order->created_at->format('d M Y') }} · Sold by {{ $order->vendor->business_name ?? '—' }}
                </div>
            </div>
            <span class="badge bg-{{ $order->status->badge() }}-subtle text-{{ $order->status->badge() }} fs-6">{{ $order->status->label() }}</span>
        </div>
    </div>

    @if(! $order->isPaid())
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span><i class="bi bi-exclamation-circle me-1"></i>This order is awaiting payment.</span>
            <a href="{{ route('checkout.product', $order->items->first()->product_id ?? 0) }}" class="btn btn-sm btn-warning">Complete payment</a>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Items</div>
        <ul class="list-group list-group-flush">
            @foreach($order->items as $item)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-semibold">{{ $item->name }}</div>
                            <div class="small text-muted">Qty {{ $item->quantity }} · {{ money($item->unit_price) }}</div>
                        </div>
                        <div class="fw-semibold text-nowrap">{{ money($item->subtotal) }}</div>
                    </div>

                    @if($order->isPaid() && $item->product && $item->product->files->isNotEmpty())
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            @foreach($item->product->files as $file)
                                <button type="button" class="btn btn-sm btn-outline-primary vlm-download"
                                        data-url="{{ route('products.download.link', [$order, $file]) }}">
                                    <i class="bi bi-download me-1"></i>{{ $file->label ?: ($file->original_name ?: 'Download file') }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="card-footer bg-white d-flex justify-content-between">
            <span class="text-muted">Total</span>
            <span class="fw-bold">{{ money($order->total_amount) }}</span>
        </div>
    </div>

    @if($order->isPaid() && ! $order->isCompleted())
        <div class="card border-success-subtle">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <div class="fw-semibold">Received everything?</div>
                    <div class="small text-muted">Confirming releases your payment from escrow to the seller.</div>
                </div>
                <div class="d-flex gap-2">
                    @if($order->canBeDisputed())
                        <a href="{{ route('escrows.index') }}" class="btn btn-outline-danger btn-sm">Raise an issue</a>
                    @endif
                    <form method="POST" action="{{ route('orders.complete', $order) }}"
                          onsubmit="return confirm('Confirm receipt and release payment to the seller?');">
                        @csrf
                        <button class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Confirm &amp; release</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.vlm-download').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Preparing…';
        fetch(btn.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(data => { window.location.href = data.url; })
        .catch(() => alert('Could not generate a download link. Please try again.'))
        .finally(() => { btn.disabled = false; btn.innerHTML = original; });
    });
});
</script>
@endpush
