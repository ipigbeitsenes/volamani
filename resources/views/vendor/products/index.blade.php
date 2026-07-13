@extends('layouts.vendor')

@section('title', 'My Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">My Products</h4>
    <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Product
    </a>
</div>

@if($products->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-box-seam fs-1 text-muted"></i>
            <h5 class="mt-3">No products yet</h5>
            <p class="text-muted">Start selling by adding your first digital product.</p>
            <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">Add Your First Product</a>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Sales</th>
                        <th>Rating</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $product->thumbnail_url }}"
                                         class="rounded bg-light border"
                                         style="width:48px;height:48px;object-fit:contain;"
                                         alt="{{ $product->name }}">
                                    <div>
                                        <div class="fw-semibold">{{ Str::limit($product->name, 45) }}</div>
                                        <small class="text-muted">{{ $product->category->name ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $product->type->label() }}</span></td>
                            <td class="fw-semibold">{{ money($product->price) }}</td>
                            <td>
                                <span class="badge bg-{{ $product->status->badge() }}">
                                    {{ $product->status->label() }}
                                </span>
                                @if($product->isPromoted())
                                    <span class="badge bg-warning text-dark" title="Featured until {{ $product->featured_until->format('d M Y') }}">
                                        <i class="bi bi-star-fill"></i> Featured
                                    </span>
                                @endif
                            </td>
                            <td>{{ number_format($product->sales_count) }}</td>
                            <td>
                                @if($product->reviews_count > 0)
                                    <span class="text-warning small">
                                        <i class="bi bi-star-fill"></i>
                                    </span>
                                    {{ number_format($product->average_rating, 1) }}
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown">Actions</button>
                                    <ul class="dropdown-menu">
                                        @if($product->isActive())
                                            <li>
                                                <a class="dropdown-item" href="{{ route('marketplace.products.show', $product->slug) }}" target="_blank">
                                                    <i class="bi bi-eye me-2"></i>View Listing
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="{{ route('vendor.products.edit', $product->id) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        @if($product->isActive())
                                            <li>
                                                <form action="{{ route('vendor.products.promote', $product->id) }}" method="POST"
                                                      onsubmit="return confirm('Promote this product for {{ config('payment.promotion.days') }} days for {{ money(config('payment.promotion.fee')) }}? The promotion fee will be charged to your account.')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-star me-2 text-warning"></i>{{ $product->isPromoted() ? 'Extend Promotion' : 'Promote' }} ({{ money(config('payment.promotion.fee')) }})
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('vendor.products.destroy', $product->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Archive this product? It will no longer be visible.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-archive me-2"></i>Archive
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $products->links() }}
    </div>
@endif
@endsection
