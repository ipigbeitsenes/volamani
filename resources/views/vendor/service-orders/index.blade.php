@extends('layouts.vendor')

@section('title', 'Service Orders Received')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Service Orders</h4>
</div>

@if($orders->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-inbox fs-1 text-muted"></i>
            <h5 class="mt-3">No orders yet</h5>
            <p class="text-muted">Orders for your services will appear here.</p>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Service</th>
                        <th>Buyer</th>
                        <th>Package</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td class="font-monospace small">{{ $order->reference }}</td>
                            <td>{{ Str::limit($order->service->title, 35) }}</td>
                            <td class="small">{{ $order->buyer->name }}</td>
                            <td>
                                <span class="badge bg-{{ $order->package->tier->badge() }}">
                                    {{ $order->package->tier->label() }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ money($order->vendor_earnings) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->status->badge() }}">
                                    {{ $order->status->label() }}
                                </span>
                                @if($order->isOverdue())
                                    <span class="badge bg-danger ms-1">Overdue</span>
                                @endif
                            </td>
                            <td class="small {{ $order->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                                {{ $order->due_at ? $order->due_at->format('M j') : '—' }}
                            </td>
                            <td>
                                <a href="{{ route('vendor.service-orders.show', $order->id) }}"
                                   class="btn btn-sm btn-outline-primary">Manage</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
@endif
@endsection
