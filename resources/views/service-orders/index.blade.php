@extends('layouts.app')

@section('title', 'My Service Orders')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4">My Service Orders</h4>

    @if($orders->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-briefcase fs-1 text-muted"></i>
                <h5 class="mt-3">No service orders yet</h5>
                <p class="text-muted">Browse services and place your first order.</p>
                <a href="{{ route('marketplace.services.index') }}" class="btn btn-primary">Browse Services</a>
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
                            <th>Vendor</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td class="font-monospace small">{{ $order->reference }}</td>
                                <td>{{ Str::limit($order->service->title, 40) }}</td>
                                <td class="small">{{ $order->vendor->business_name }}</td>
                                <td class="fw-semibold">{{ money($order->total_amount) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->status->badge() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                    @if($order->isOverdue())
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    @endif
                                </td>
                                <td class="small">
                                    {{ $order->due_at ? $order->due_at->format('M j, Y') : '—' }}
                                </td>
                                <td>
                                    <a href="{{ route('service-orders.show', $order->id) }}"
                                       class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
