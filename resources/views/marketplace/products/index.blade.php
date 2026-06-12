@extends('layouts.app')

@section('title', 'Digital Products — Volamani Marketplace')

@section('content')
<div class="container-fluid py-4">
    <div class="row">

        {{-- Sidebar Filters --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Filter Products</div>
                <div class="card-body">
                    <form action="{{ route('marketplace.products.index') }}" method="GET" id="filterForm">
                        {{-- Search --}}
                        <div class="mb-3">
                            <input type="text" name="q" class="form-control"
                                placeholder="Search products..."
                                value="{{ $filters['q'] ?? '' }}">
                        </div>

                        {{-- Category --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Category</label>
                            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ ($filters['category'] ?? '') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                    @foreach($cat->children as $sub)
                                        <option value="{{ $sub->id }}"
                                            {{ ($filters['category'] ?? '') == $sub->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;&nbsp;{{ $sub->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        {{-- Product Type --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Type</label>
                            @foreach(\App\Enums\ProductType::cases() as $type)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type"
                                        id="type_{{ $type->value }}" value="{{ $type->value }}"
                                        {{ ($filters['type'] ?? '') === $type->value ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <label class="form-check-label small" for="type_{{ $type->value }}">
                                        {{ $type->label() }}
                                    </label>
                                </div>
                            @endforeach
                            @if(!empty($filters['type']))
                                <a href="{{ request()->fullUrlWithoutQuery(['type']) }}" class="small text-muted">Clear</a>
                            @endif
                        </div>

                        {{-- Price Range --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Price (₦)</label>
                            <div class="row g-1">
                                <div class="col">
                                    <input type="number" name="min_price" class="form-control form-control-sm"
                                        placeholder="Min" value="{{ $filters['min_price'] ?? '' }}" min="0">
                                </div>
                                <div class="col">
                                    <input type="number" name="max_price" class="form-control form-control-sm"
                                        placeholder="Max" value="{{ $filters['max_price'] ?? '' }}" min="0">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filters</button>
                        <a href="{{ route('marketplace.products.index') }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear All</a>
                    </form>
                </div>
            </div>
        </div>

        {{-- Products Grid --}}
        <div class="col-lg-9">

            {{-- Sort & Count Bar --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0 text-muted small">
                    Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
                    of {{ $products->total() }} products
                </p>
                <select name="sort" form="filterForm" class="form-select form-select-sm w-auto" onchange="document.getElementById('filterForm').submit()">
                    <option value="latest" {{ ($filters['sort'] ?? 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="popular" {{ ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                    <option value="top_rated" {{ ($filters['sort'] ?? '') === 'top_rated' ? 'selected' : '' }}>Top Rated</option>
                    <option value="price_asc" {{ ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                </select>
            </div>

            @if($products->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-box-seam fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">No products found. Try adjusting your filters.</p>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @foreach($products as $product)
                        <div class="col">
                            @include('marketplace.products._card', ['product' => $product])
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
