@extends('layouts.app')

@section('title', 'Products — Volamani Marketplace')

@section('content')
<div class="container-fluid py-4">
    <div class="row">

        {{-- Sidebar Filters --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center" role="button"
                     data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <span><i class="bi bi-funnel me-1"></i>Filter Products</span>
                    <i class="bi bi-chevron-down d-lg-none"></i>
                </div>
                <div class="collapse d-lg-block" id="filterCollapse">
                <div class="card-body">
                    <form action="{{ route('marketplace.products.index') }}" method="GET" id="filterForm">
                        {{-- Search (covers digital + physical by name/description) --}}
                        <div class="mb-3">
                            <input type="text" name="q" class="form-control"
                                placeholder="Search all products..."
                                value="{{ $filters['q'] ?? '' }}">
                        </div>

                        {{-- Kind: digital vs physical --}}
                        @php $kind = $filters['kind'] ?? ''; @endphp
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Product kind</label>
                            <div class="btn-group w-100" role="group" id="kindGroup">
                                <input type="radio" class="btn-check" name="kind" id="kind_all" value="" {{ $kind === '' ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="btn btn-outline-primary btn-sm" for="kind_all">All</label>
                                <input type="radio" class="btn-check" name="kind" id="kind_digital" value="digital" {{ $kind === 'digital' ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="btn btn-outline-primary btn-sm" for="kind_digital">Digital</label>
                                <input type="radio" class="btn-check" name="kind" id="kind_physical" value="physical" {{ $kind === 'physical' ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="btn btn-outline-primary btn-sm" for="kind_physical">Physical</label>
                            </div>
                        </div>

                        {{-- Digital category + type (hidden when kind=physical) --}}
                        <div data-kind-block="digital" class="{{ $kind === 'physical' ? 'd-none' : '' }}">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Digital category</label>
                                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All digital categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ ($filters['category'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @foreach($cat->children as $sub)
                                            <option value="{{ $sub->id }}" {{ ($filters['category'] ?? '') == $sub->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;{{ $sub->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Digital type</label>
                                @foreach(\App\Enums\ProductType::cases() as $type)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type"
                                            id="type_{{ $type->value }}" value="{{ $type->value }}"
                                            {{ ($filters['type'] ?? '') === $type->value ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <label class="form-check-label small" for="type_{{ $type->value }}">{{ $type->label() }}</label>
                                    </div>
                                @endforeach
                                @if(!empty($filters['type']))
                                    <a href="{{ request()->fullUrlWithoutQuery(['type']) }}" class="small text-muted">Clear type</a>
                                @endif
                            </div>
                        </div>

                        {{-- Physical category + stock (hidden when kind=digital) --}}
                        <div data-kind-block="physical" class="{{ $kind === 'digital' ? 'd-none' : '' }}">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Physical category</label>
                                <select name="physical_category" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All physical categories</option>
                                    @foreach($physicalCategories as $cat)
                                        <option value="{{ $cat->id }}" {{ ($filters['physical_category'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @foreach($cat->children as $sub)
                                            <option value="{{ $sub->id }}" {{ ($filters['physical_category'] ?? '') == $sub->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;{{ $sub->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 form-check">
                                <input class="form-check-input" type="checkbox" name="in_stock" value="1"
                                    id="inStock" {{ !empty($filters['in_stock']) ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label small" for="inStock">In stock only</label>
                            </div>
                        </div>

                        {{-- Price Range --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Price ({{ currency_symbol() }})</label>
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
                </div>{{-- /#filterCollapse --}}
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
