@extends('layouts.app')

@section('title', 'Browse Stores')
@section('meta_description', 'Discover and follow verified vendors on Volamani. Browse stores by popularity, rating and category.')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-shop text-primary me-2"></i>Browse Stores</h4>
            <p class="text-muted mb-0 small">Discover sellers and follow them to hear about new listings first.</p>
        </div>
        @auth
            <a href="{{ route('follow.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-person-heart me-1"></i>Stores you follow
            </a>
        @endauth
    </div>

    {{-- Search + sort --}}
    <form method="GET" action="{{ route('vendors.index') }}" class="row g-2 mb-4">
        <div class="col-md-7">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                       placeholder="Search stores by name, tagline or category…">
            </div>
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select" onchange="this.form.submit()">
                @foreach(['popular' => 'Most followed', 'rating' => 'Top rated', 'newest' => 'Newest'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('sort', 'popular') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
        </div>
    </form>

    {{-- Grid --}}
    @if($vendors->count())
        <div class="row g-3">
            @foreach($vendors as $vendor)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex gap-3">
                        <img src="{{ $vendor->logo_url }}" class="rounded-3 flex-shrink-0 bg-white border" width="56" height="56"
                             style="object-fit:contain;padding:2px" alt="{{ $vendor->business_name }}">
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('storefront.show', $vendor->user->username ?? $vendor->slug) }}"
                                   class="fw-semibold text-dark text-decoration-none text-truncate">{{ $vendor->business_name }}</a>
                                @if($vendor->isVerified())
                                    <i class="bi bi-patch-check-fill text-success small" title="Verified"></i>
                                @endif
                            </div>
                            @if($vendor->tagline)
                                <p class="text-muted small mb-1 text-truncate">{{ $vendor->tagline }}</p>
                            @endif
                            <div class="d-flex flex-wrap gap-3 text-muted" style="font-size:.78rem;">
                                <span><i class="bi bi-box-seam me-1"></i>{{ $vendor->active_products_count }} products</span>
                                <span><i class="bi bi-people me-1"></i>{{ number_format($vendor->followers_count) }} followers</span>
                                @if($vendor->average_rating > 0)
                                    <span><i class="bi bi-star-fill text-warning me-1"></i>{{ number_format($vendor->average_rating, 1) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 d-flex gap-2">
                        <a href="{{ route('storefront.show', $vendor->user->username ?? $vendor->slug) }}"
                           class="btn btn-sm btn-outline-secondary flex-grow-1">Visit store</a>
                        @include('social._follow_button', ['vendor' => $vendor])
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $vendors->links() }}
        </div>
    @else
        <div class="text-center text-muted py-5">
            <i class="bi bi-shop d-block fs-1 mb-2 opacity-25"></i>
            <p class="mb-3">
                @if(request('q'))
                    No stores match “{{ request('q') }}”.
                @else
                    No stores to show yet.
                @endif
            </p>
            <a href="{{ route('vendors.index') }}" class="btn btn-outline-primary btn-sm">Clear search</a>
        </div>
    @endif
</div>
@endsection
