@extends('layouts.account')

@section('title', 'My Orders')

@section('content')
<div class="container py-4" style="max-width: 920px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">My Orders</h1>
        <a href="{{ route('returns.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-return-left me-1"></i>My Returns</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">Order</th><th>Seller</th><th>Items</th><th>Total</th><th>Status</th><th class="text-end pe-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-semibold">{{ $order->reference }}</span>
                                    <span class="d-block small text-muted">{{ $order->created_at->format('d M Y') }}</span>
                                </td>
                                <td class="small">{{ $order->vendor->business_name ?? '—' }}</td>
                                <td class="small text-muted">{{ $order->items_count }}</td>
                                <td class="fw-semibold">{{ money($order->total_amount) }}</td>
                                <td><span class="badge bg-{{ $order->status->badge() }}-subtle text-{{ $order->status->badge() }}">{{ $order->status->label() }}</span></td>
                                <td class="text-end pe-3"><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-bag display-6 d-block mb-2"></i>
                                You haven't placed any orders yet.
                                <div class="mt-2"><a href="{{ route('marketplace.products.index') }}" class="btn btn-sm btn-primary">Browse products</a></div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
