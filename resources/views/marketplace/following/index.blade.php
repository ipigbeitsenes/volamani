@extends('layouts.account')

@section('title', 'Sellers You Follow')

@section('content')
<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-heart text-danger me-2"></i>Following</h4>
            <p class="text-muted mb-0 small">Stores you follow. We'll notify you when they launch new products.</p>
        </div>
        <a href="{{ route('vendors.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-search me-1"></i>Discover more sellers
        </a>
    </div>

    @if($vendors->count())
        <div class="row g-3">
            @foreach($vendors as $vendor)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex gap-3">
                        <img src="{{ $vendor->logo_url }}" class="rounded-3 flex-shrink-0 bg-white border" width="56" height="56"
                             style="object-fit:contain;padding:2px" alt="{{ $vendor->business_name }}">
                        <div class="flex-grow-1 min-w-0">
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
                            <div class="d-flex gap-3 text-muted" style="font-size:.78rem;">
                                <span><i class="bi bi-box-seam me-1"></i>{{ $vendor->active_products_count }} products</span>
                                <span><i class="bi bi-people me-1"></i>{{ number_format($vendor->followers_count) }} followers</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 d-flex gap-2">
                        <a href="{{ route('storefront.show', $vendor->user->username ?? $vendor->slug) }}"
                           class="btn btn-sm btn-outline-secondary flex-grow-1">Visit store</a>
                        <form method="POST" action="{{ route('follow.toggle', $vendor) }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light" title="Unfollow">
                                <i class="bi bi-person-check-fill text-primary"></i>
                            </button>
                        </form>
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
            <i class="bi bi-person-heart d-block fs-1 mb-2"></i>
            <p class="mb-3">You're not following any sellers yet.</p>
            <a href="{{ route('vendors.index') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-shop me-1"></i>Browse stores to follow
            </a>
        </div>
    @endif
</div>
@endsection
