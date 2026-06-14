@extends('layouts.app')

@section('title', 'Your Cart')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-cart3 me-2"></i>Your Cart</h4>

    @if(empty($summary['groups']))
        <div class="text-center py-5 bg-white rounded shadow-sm">
            <i class="bi bi-cart-x display-5 text-muted d-block mb-3"></i>
            <p class="text-muted mb-3">Your cart is empty.</p>
            <a href="{{ route('marketplace.products.index') }}" class="btn btn-primary">
                <i class="bi bi-grid me-1"></i>Browse Products
            </a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                @foreach($summary['groups'] as $group)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white d-flex align-items-center gap-2">
                            <i class="bi bi-shop text-primary"></i>
                            <span class="fw-semibold">{{ $group['vendor']?->business_name ?? 'Volamani' }}</span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table align-middle mb-0">
                                <tbody>
                                @foreach($group['lines'] as $line)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $line['name'] }}</div>
                                            <small class="text-muted">
                                                {{ $line['kind'] === 'physical' ? 'Physical · ships separately' : ($line['kind'] === 'service' ? 'Service' : 'Digital') }}
                                            </small>
                                            @if(! ($line['in_stock'] ?? true))
                                                <span class="badge bg-danger ms-1">Out of stock</span>
                                            @endif
                                        </td>
                                        <td style="width: 140px;">
                                            @if($line['kind'] === 'product')
                                                <form method="POST" action="{{ route('cart.products.update', $line['id']) }}" class="d-flex align-items-center gap-1">
                                                    @csrf @method('PATCH')
                                                    <input type="number" name="qty" value="{{ $line['qty'] }}" min="1" class="form-control form-control-sm" style="width: 70px;">
                                                    <button class="btn btn-sm btn-outline-secondary" title="Update"><i class="bi bi-arrow-repeat"></i></button>
                                                </form>
                                            @elseif($line['kind'] === 'physical')
                                                <form method="POST" action="{{ route('cart.physical.update', $line['id']) }}" class="d-flex align-items-center gap-1">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="variant_id" value="{{ $line['variant_id'] }}">
                                                    <input type="number" name="qty" value="{{ $line['qty'] }}" min="1" class="form-control form-control-sm" style="width: 70px;">
                                                    <button class="btn btn-sm btn-outline-secondary" title="Update"><i class="bi bi-arrow-repeat"></i></button>
                                                </form>
                                            @else
                                                <span class="text-muted small">Qty 1</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold">{{ money($line['subtotal']) }}</td>
                                        <td class="text-end" style="width: 50px;">
                                            @php
                                                $removeAction = match($line['kind']) {
                                                    'product'  => route('cart.products.remove', $line['id']),
                                                    'physical' => route('cart.physical.remove', $line['id']),
                                                    default    => route('cart.services.remove', $line['id']),
                                                };
                                            @endphp
                                            <form method="POST" action="{{ $removeAction }}">
                                                @csrf @method('DELETE')
                                                @if($line['kind'] === 'physical')
                                                    <input type="hidden" name="variant_id" value="{{ $line['variant_id'] }}">
                                                @endif
                                                <button class="btn btn-sm btn-link text-danger p-0" title="Remove"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white text-end small">
                            Subtotal: <span class="fw-semibold">{{ money($group['subtotal']) }}</span>
                        </div>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Clear cart</button>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 90px;">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Order Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Items</span>
                            <span>{{ collect($summary['groups'])->sum(fn($g) => count($g['lines'])) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Sellers</span>
                            <span>{{ count($summary['groups']) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Subtotal</span>
                            <span>{{ money($summary['total']) }}</span>
                        </div>
                        @if($shipping > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Shipping</span>
                                <span>{{ money($shipping) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                            <span>Total</span>
                            <span class="text-primary">{{ money($grandTotal) }}</span>
                        </div>
                        @auth
                            <a href="{{ route('cart.checkout') }}" class="btn btn-primary w-100">
                                <i class="bi bi-lock me-1"></i>Proceed to Checkout
                            </a>
                        @else
                            <a href="{{ route('login') }}?redirect={{ urlencode(route('cart.checkout')) }}" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Sign in to Checkout
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
