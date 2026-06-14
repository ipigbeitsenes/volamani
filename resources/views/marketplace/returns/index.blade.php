@extends('layouts.app')

@section('title', 'My Returns')

@section('content')
<div class="container py-4" style="max-width: 820px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-arrow-return-left me-2"></i>My Returns</h4>
        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">My Orders</a>
    </div>

    @if($returns->isEmpty())
        <div class="text-center py-5 bg-white rounded shadow-sm">
            <i class="bi bi-arrow-return-left display-6 text-muted d-block mb-3"></i>
            <p class="text-muted mb-0">You haven't requested any returns.</p>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <ul class="list-group list-group-flush">
                @foreach($returns as $r)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('orders.show', $r->order_id) }}" class="fw-semibold text-decoration-none">{{ $r->reference }}</a>
                            <div class="small text-muted">
                                Order {{ $r->order->reference ?? '—' }} · {{ $r->reason->label() }} · {{ $r->created_at->format('d M Y') }}
                                @if($r->refunded_amount) · Refunded {{ money($r->refunded_amount) }}@endif
                            </div>
                        </div>
                        <span class="badge bg-{{ $r->status->badge() }}">{{ $r->status->label() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="mt-3">{{ $returns->links() }}</div>
    @endif
</div>
@endsection
