@extends('layouts.account')

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

    @if($order->status === \App\Enums\OrderStatus::Cancelled)
        <div class="alert alert-secondary">
            <div class="fw-semibold"><i class="bi bi-x-circle me-1"></i>This order was cancelled by the seller.</div>
            @if($order->cancellation_reason)<div class="small mt-1">Reason: {{ $order->cancellation_reason }}</div>@endif
            <div class="small text-muted mt-1">Any payment has been refunded to your <a href="{{ route('wallet.index') }}">Volamani wallet</a>.</div>
        </div>
    @endif

    @if(! $order->isPaid())
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span><i class="bi bi-exclamation-circle me-1"></i>This order is awaiting payment.</span>
            @if(! $order->requires_shipping)
                <a href="{{ route('checkout.product', $order->items->first()->product_id ?? 0) }}" class="btn btn-sm btn-warning">Complete payment</a>
            @endif
        </div>
    @endif

    @if($order->requires_shipping)
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span>Shipping</span>
                @if($order->shipped_at)
                    <span class="badge bg-info-subtle text-info">Shipped {{ $order->shipped_at->format('d M') }}</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="small text-muted mb-1">Deliver to</div>
                        @foreach($order->shippingAddressLines() as $line)
                            <div>{{ $line }}</div>
                        @endforeach
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted mb-1">Tracking</div>
                        @if($order->tracking_number)
                            <div class="fw-semibold">{{ $order->tracking_number }}</div>
                            @if($order->courier)<div class="small text-muted">{{ $order->courier }}</div>@endif
                        @else
                            <div class="text-muted">{{ $order->shipped_at ? 'No tracking number provided' : 'Not shipped yet' }}</div>
                        @endif
                        <div class="d-flex justify-content-between small mt-2">
                            <span class="text-muted">Shipping fee</span>
                            <span>{{ $order->shipping_fee ? money($order->shipping_fee) : 'Free' }}</span>
                        </div>
                    </div>
                </div>
            </div>
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

                    @if($order->isPaid() && $item->product && $item->product->isDigital() && $item->product->files->isNotEmpty())
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

    {{-- Returns / RMA (physical orders) --}}
    @if($order->requires_shipping)
        @php $activeReturn = $order->activeReturn(); $latestReturn = $order->returnRequests->first(); @endphp
        @if($activeReturn)
            <div class="card mt-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-arrow-return-left me-1"></i>Return {{ $activeReturn->reference }}</span>
                    <span class="badge bg-{{ $activeReturn->status->badge() }}">{{ $activeReturn->status->label() }}</span>
                </div>
                <div class="card-body">
                    <div class="small text-muted mb-2">Reason: {{ $activeReturn->reason->label() }}</div>
                    @if($activeReturn->decision_note)
                        <div class="small mb-2"><strong>Seller note:</strong> {{ $activeReturn->decision_note }}</div>
                    @endif
                    @if($activeReturn->canMarkShipped())
                        <form method="POST" action="{{ route('returns.shipped', $activeReturn) }}" class="row g-2 align-items-end mb-2">
                            @csrf
                            <div class="col-sm-8">
                                <label class="form-label small">Return tracking <span class="text-muted">(optional)</span></label>
                                <input name="return_tracking" class="form-control form-control-sm" placeholder="Courier tracking number">
                            </div>
                            <div class="col-sm-4"><button class="btn btn-primary btn-sm w-100">I've shipped it back</button></div>
                        </form>
                    @elseif($activeReturn->status === \App\Enums\ReturnStatus::ShippedBack)
                        <div class="small text-muted">Awaiting the seller to confirm receipt and refund.</div>
                    @endif
                    @if($activeReturn->canCancel())
                        <form method="POST" action="{{ route('returns.cancel', $activeReturn) }}">
                            @csrf
                            <button class="btn btn-link btn-sm text-muted p-0">Cancel return</button>
                        </form>
                    @endif
                </div>
            </div>
        @elseif($order->canRequestReturn())
            <div class="card mt-3">
                <div class="card-body">
                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#returnForm">
                        <i class="bi bi-arrow-return-left me-1"></i>Request a return
                    </button>
                    <span class="small text-muted ms-2">Return window closes {{ $order->returnWindowClosesAt()->format('d M Y') }}.</span>
                    <div class="collapse mt-3" id="returnForm">
                        <form method="POST" action="{{ route('returns.store', $order) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small">Reason</label>
                                <select name="reason" class="form-select form-select-sm" required>
                                    @foreach(\App\Enums\ReturnReason::cases() as $r)
                                        <option value="{{ $r->value }}">{{ $r->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">What's wrong? <span class="text-danger">*</span></label>
                                <textarea name="description" rows="3" minlength="10" class="form-control form-control-sm" required
                                          placeholder="Describe the problem (at least 10 characters)"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Photos <span class="text-muted">(optional, up to 5)</span></label>
                                <input type="file" name="photos[]" accept="image/*" multiple class="form-control form-control-sm">
                            </div>
                            <button class="btn btn-danger btn-sm">Submit return request</button>
                        </form>
                    </div>
                </div>
            </div>
        @elseif($latestReturn && in_array($latestReturn->status, [\App\Enums\ReturnStatus::Refunded, \App\Enums\ReturnStatus::Rejected, \App\Enums\ReturnStatus::Cancelled], true))
            <div class="alert mt-3 alert-{{ $latestReturn->status === \App\Enums\ReturnStatus::Refunded ? 'success' : 'secondary' }}">
                Return {{ $latestReturn->reference }} — {{ $latestReturn->status->label() }}.
                @if($latestReturn->decision_note) {{ $latestReturn->decision_note }}@endif
            </div>
        @endif
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
