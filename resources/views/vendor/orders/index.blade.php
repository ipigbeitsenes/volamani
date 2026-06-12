@extends('layouts.vendor')

@section('title', 'Orders Received')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Orders Received</h4>
        <p class="text-muted mb-0">Product orders placed with your store.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">Order</th><th>Buyer</th><th>Items</th><th>Earnings</th><th>Status</th><th class="text-end pe-3"></th></tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-semibold">{{ $order->reference }}</span>
                                    <span class="d-block small text-muted">{{ $order->created_at->format('d M Y') }}</span>
                                </td>
                                <td class="small">{{ $order->buyer->name ?? '—' }}</td>
                                <td class="small text-muted">{{ $order->items_count }}</td>
                                <td class="fw-semibold">{{ money($order->vendor_earnings) }}</td>
                                <td><span class="badge bg-{{ $order->status->badge() }}-subtle text-{{ $order->status->badge() }}">{{ $order->status->label() }}</span></td>
                                <td class="text-end pe-3"><a href="{{ route('vendor.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
