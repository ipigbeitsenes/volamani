@extends('layouts.app')

@section('title', $product->seo_title ?? $product->name . ' — Volamani')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('marketplace.products.index') }}">Products</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('marketplace.products.index', ['category' => $product->category_id]) }}">
                    {{ $product->category->name }}
                </a>
            </li>
            <li class="breadcrumb-item active">{{ Str::limit($product->name, 50) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Left: Media --}}
        <div class="col-lg-7">
            {{-- Main Thumbnail --}}
            <div class="mb-3 rounded overflow-hidden" style="max-height: 400px;">
                <img id="mainImage" src="{{ $product->thumbnail_url }}"
                     alt="{{ $product->name }}"
                     class="img-fluid w-100" style="object-fit: cover; max-height: 400px;">
            </div>

            {{-- Gallery --}}
            @if($product->gallery->count() > 0)
                <div class="d-flex gap-2 flex-wrap">
                    <img src="{{ $product->thumbnail_url }}"
                         class="gallery-thumb rounded border cursor-pointer"
                         style="width:70px;height:70px;object-fit:cover;"
                         onclick="document.getElementById('mainImage').src=this.src">
                    @foreach($product->gallery as $img)
                        <img src="{{ $img->url }}"
                             class="gallery-thumb rounded border cursor-pointer"
                             style="width:70px;height:70px;object-fit:cover;"
                             onclick="document.getElementById('mainImage').src=this.src">
                    @endforeach
                </div>
            @endif

            {{-- Preview URL --}}
            @if($product->preview_url)
                <div class="mt-3">
                    <a href="{{ $product->preview_url }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-play-circle me-1"></i> Preview / Demo
                    </a>
                </div>
            @endif

            {{-- Description Tabs --}}
            <div class="mt-4">
                <ul class="nav nav-tabs" id="productTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#description">Description</a>
                    </li>
                    @if($product->files->count() > 0 && $hasPurchased)
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#downloads">Downloads</a>
                        </li>
                    @endif
                </ul>
                <div class="tab-content border border-top-0 rounded-bottom p-3">
                    <div class="tab-pane fade show active" id="description">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                    @if($product->files->count() > 0 && $hasPurchased)
                        <div class="tab-pane fade" id="downloads">
                            <p class="text-success fw-semibold">
                                <i class="bi bi-check-circle me-1"></i> You have purchased this product
                            </p>
                            {{-- Download links rendered via JS after AJAX call --}}
                            <div id="downloadLinks">
                                <p class="text-muted small">Click a file below to get a secure download link.</p>
                                @foreach($product->files as $file)
                                    <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                                        <div>
                                            <i class="bi bi-file-earmark-arrow-down me-2"></i>
                                            <span>{{ $file->label }}</span>
                                            <small class="text-muted ms-2">({{ $file->file_size_formatted }})</small>
                                        </div>
                                        <button class="btn btn-sm btn-primary download-btn"
                                            data-file-id="{{ $file->id }}"
                                            data-order-id="">
                                            Download
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Purchase Panel --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <h1 class="h4 fw-bold mb-1">{{ $product->name }}</h1>

                    @if($product->short_description)
                        <p class="text-muted small mb-3">{{ $product->short_description }}</p>
                    @endif

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="fs-3 fw-bold text-primary">{{ money($product->price) }}</span>
                        @if($product->hasDiscount())
                            <span class="text-muted text-decoration-line-through fs-5">{{ money($product->compare_price) }}</span>
                            <span class="badge bg-danger">-{{ $product->discountPercent() }}%</span>
                        @endif
                    </div>

                    {{-- Ratings --}}
                    @if($product->reviews_count > 0)
                        <div class="mb-3 d-flex align-items-center gap-2">
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= round($product->average_rating) ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            <span class="fw-semibold">{{ number_format($product->average_rating, 1) }}</span>
                            <span class="text-muted small">({{ $product->reviews_count }} reviews)</span>
                        </div>
                    @endif

                    {{-- Meta --}}
                    <ul class="list-unstyled small text-muted mb-3">
                        <li><i class="bi bi-tag me-2"></i>Type: {{ $product->type->label() }}</li>
                        <li><i class="bi bi-grid me-2"></i>Category: {{ $product->category->name }}</li>
                        @if($product->is_downloadable)
                            <li><i class="bi bi-download me-2"></i>Instant download after purchase</li>
                            @if($product->download_limit)
                                <li><i class="bi bi-arrow-repeat me-2"></i>Download limit: {{ $product->download_limit }} times</li>
                            @endif
                        @endif
                        <li><i class="bi bi-bag-check me-2"></i>{{ number_format($product->sales_count) }} sales</li>
                    </ul>

                    @if($hasPurchased)
                        <div class="alert alert-success py-2">
                            <i class="bi bi-check-circle me-1"></i> You already own this product.
                            <a href="#downloads" data-bs-toggle="tab" class="alert-link">Go to downloads</a>
                        </div>
                    @else
                        @if(auth()->check())
                            <a href="{{ route('checkout.product', $product->id) }}"
                               class="btn btn-primary w-100 btn-lg mb-2">
                                Buy Now — {{ money($product->price) }}
                            </a>
                        @else
                            <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}"
                               class="btn btn-primary w-100 btn-lg mb-2">
                                Login to Purchase
                            </a>
                        @endif
                        <form method="POST" action="{{ route('cart.products.add', $product->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-cart-plus me-1"></i>Add to Cart
                            </button>
                        </form>
                    @endif

                    <div class="border-top pt-3 mt-3 d-flex align-items-center gap-2">
                        <a href="{{ route('storefront.show', $product->vendor->user->username) }}"
                           class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                            <img src="{{ $product->vendor->logo_url }}"
                                 class="rounded-circle"
                                 style="width:40px;height:40px;object-fit:cover;"
                                 alt="{{ $product->vendor->business_name }}">
                            <div>
                                <div class="fw-semibold small">{{ $product->vendor->business_name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-people me-1"></i>{{ number_format($product->vendor->followers_count) }} followers · View store
                                </div>
                            </div>
                        </a>
                        <div class="ms-auto">
                            @include('social._follow_button', ['vendor' => $product->vendor])
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tags --}}
            @if($product->tags->count() > 0)
                <div class="mt-3">
                    @foreach($product->tags as $tag)
                        <a href="{{ route('marketplace.products.index', ['q' => $tag->name]) }}"
                           class="badge bg-light text-dark border text-decoration-none me-1">
                            {{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Related Products --}}
    @if($related->count() > 0)
        <div class="mt-5">
            <h4 class="fw-bold mb-3">Related Products</h4>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                @foreach($related as $relProduct)
                    <div class="col">
                        @include('marketplace.products._card', ['product' => $relProduct])
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Reviews --}}
    <div class="mt-5" id="reviews">
        <h4 class="fw-bold mb-3">Reviews</h4>
        @auth
            @if($product->canBeReviewedBy(auth()->user()))
                @include('reviews._form', ['type' => 'product', 'reviewableId' => $product->id])
            @endif
        @endauth
        @include('reviews._list', ['reviews' => $product->reviews()->with('reviewer')->latest()->get()])
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.download-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const fileId  = this.dataset.fileId;
        const orderId = this.dataset.orderId;
        if (!orderId) {
            alert('Please access downloads from your orders page.');
            return;
        }
        try {
            const res = await fetch(`/orders/${orderId}/download/${fileId}/link`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
            });
            const data = await res.json();
            if (data.url) window.location.href = data.url;
        } catch(e) {
            alert('Could not generate download link. Please try from your orders page.');
        }
    });
});
</script>
@endpush
@endsection
