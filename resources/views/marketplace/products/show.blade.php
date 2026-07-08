@extends('layouts.app')

@php
    $seoName  = $product->seo_title ?: $product->name;
    $seoDesc  = \Illuminate\Support\Str::limit(strip_tags($product->seo_description ?: ($product->short_description ?: $product->description)), 155);
    $seoImg   = $product->thumbnail_url;
    if ($seoImg && ! \Illuminate\Support\Str::startsWith($seoImg, ['http://', 'https://', '//', 'data:'])) {
        $seoImg = url($seoImg);
    }
    $seoAvailable = $product->isDigital() || $product->inStock();
    $seoReviewCount = (int) ($product->reviews_count ?? $product->reviews()->count());
@endphp

@section('title', $seoName)
@section('meta_description', $seoDesc)
@section('og_type', 'product')
@section('og_image', $seoImg)

@push('schema')
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type'    => 'Product',
    'name'     => $product->name,
    'description' => $seoDesc,
    'image'    => $seoImg,
    'sku'      => (string) $product->id,
    'category' => $product->displayCategory(),
    'brand'    => $product->vendor ? ['@type' => 'Brand', 'name' => $product->vendor->business_name] : null,
    'offers'   => [
        '@type'         => 'Offer',
        'url'           => route('marketplace.products.show', $product->slug),
        'priceCurrency' => 'NGN',
        'price'         => number_format($product->price / 100, 2, '.', ''),
        'availability'  => 'https://schema.org/' . ($seoAvailable ? 'InStock' : 'OutOfStock'),
        'seller'        => $product->vendor ? ['@type' => 'Organization', 'name' => $product->vendor->business_name] : null,
    ],
    'aggregateRating' => ($product->average_rating > 0 && $seoReviewCount > 0) ? [
        '@type'       => 'AggregateRating',
        'ratingValue' => number_format($product->average_rating, 1),
        'reviewCount' => $seoReviewCount,
    ] : null,
], fn ($v) => $v !== null), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('marketplace.products.index') }}">Products</a></li>
            @if($product->displayCategory())
                <li class="breadcrumb-item">
                    @if($product->isDigital())
                        <a href="{{ route('marketplace.products.index', ['category' => $product->category_id]) }}">{{ $product->displayCategory() }}</a>
                    @else
                        {{ $product->displayCategory() }}
                    @endif
                </li>
            @endif
            <li class="breadcrumb-item active">{{ Str::limit($product->name, 50) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Left: Media --}}
        <div class="col-lg-7">
            {{-- Main Thumbnail --}}
            <div class="mb-3 rounded overflow-hidden bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                <img id="mainImage" src="{{ $product->thumbnail_url }}"
                     alt="{{ $product->name }}"
                     class="img-fluid mw-100" style="max-height: 400px; object-fit: contain;">
            </div>

            {{-- Gallery --}}
            @if($product->gallery->count() > 0)
                <div class="d-flex gap-2 flex-wrap">
                    <img src="{{ $product->thumbnail_url }}"
                         class="gallery-thumb rounded border cursor-pointer bg-light"
                         style="width:70px;height:70px;object-fit:contain;"
                         onclick="document.getElementById('mainImage').src=this.src">
                    @foreach($product->gallery as $img)
                        <img src="{{ $img->url }}"
                             class="gallery-thumb rounded border cursor-pointer bg-light"
                             style="width:70px;height:70px;object-fit:contain;"
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
                        @if($product->isPhysical())
                            <li><i class="bi bi-box-seam me-2"></i>Physical product —
                                <span class="badge bg-{{ $product->physicalDetail?->condition->badge() ?? 'secondary' }}">{{ $product->physicalDetail?->condition->label() ?? 'New' }}</span>
                            </li>
                            @if($product->physicalDetail?->brand)
                                <li><i class="bi bi-award me-2"></i>Brand: {{ $product->physicalDetail->brand }}</li>
                            @endif
                            @if($product->displayCategory())
                                <li><i class="bi bi-grid me-2"></i>Category: {{ $product->displayCategory() }}</li>
                            @endif
                            @if($product->physicalDetail?->weightLabel())
                                <li><i class="bi bi-speedometer me-2"></i>Weight: {{ $product->physicalDetail->weightLabel() }}</li>
                            @endif
                            <li>
                                <i class="bi bi-{{ $product->inStock() ? 'check-circle text-success' : 'x-circle text-danger' }} me-2"></i>
                                {{ $product->inStock() ? 'In stock' : 'Out of stock' }}
                                @if($product->stockQuantity() !== null && $product->inStock())
                                    <span class="text-muted">({{ number_format($product->stockQuantity()) }} available)</span>
                                @endif
                            </li>
                        @else
                            <li><i class="bi bi-tag me-2"></i>Type: {{ $product->type->label() }}</li>
                            @if($product->displayCategory())
                                <li><i class="bi bi-grid me-2"></i>Category: {{ $product->displayCategory() }}</li>
                            @endif
                            @if($product->is_downloadable)
                                <li><i class="bi bi-download me-2"></i>Instant download after purchase</li>
                                @if($product->download_limit)
                                    <li><i class="bi bi-arrow-repeat me-2"></i>Download limit: {{ $product->download_limit }} times</li>
                                @endif
                            @endif
                        @endif
                        <li><i class="bi bi-bag-check me-2"></i>{{ number_format($product->sales_count) }} sales</li>
                    </ul>

                    @if($product->isPhysical())
                        @if($product->inStock())
                            <form method="POST" action="{{ route('cart.physical.add', $product->id) }}">
                                @csrf
                                @if($product->hasVariants())
                                    <label class="form-label small fw-semibold">Choose an option</label>
                                    <div class="mb-3">
                                        @foreach($product->variants->where('is_active', true) as $variant)
                                            <div class="form-check border rounded p-2 mb-2 {{ $variant->inStock() ? '' : 'opacity-50' }}">
                                                <input class="form-check-input" type="radio" name="variant_id" id="pv{{ $variant->id }}"
                                                       value="{{ $variant->id }}" {{ $loop->first && $variant->inStock() ? 'checked' : '' }} {{ $variant->inStock() ? '' : 'disabled' }}>
                                                <label class="form-check-label d-flex justify-content-between w-100" for="pv{{ $variant->id }}">
                                                    <span>{{ $variant->name }}</span>
                                                    <span>
                                                        <span class="fw-semibold text-primary">{{ money($variant->effectivePrice()) }}</span>
                                                        <span class="badge {{ $variant->inStock() ? 'bg-success' : 'bg-secondary' }} ms-1">{{ $variant->inStock() ? $variant->stock_quantity . ' left' : 'Out' }}</span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <label class="form-label small fw-semibold mb-0">Qty</label>
                                    <input type="number" name="qty" value="1" min="1" max="999" class="form-control" style="max-width:90px;">
                                </div>
                                @auth
                                    <button type="submit" class="btn btn-outline-primary w-100 mb-2"><i class="bi bi-cart-plus me-1"></i>Add to Cart</button>
                                @else
                                    <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn btn-outline-primary w-100 mb-2">Login to Add to Cart</a>
                                @endauth
                            </form>
                            @auth
                                <a href="{{ route('checkout.physical', $product) }}" class="btn btn-primary w-100 btn-lg mb-2">
                                    <i class="bi bi-bag-check me-1"></i>Buy Now
                                </a>
                            @endauth
                            <div class="form-text mb-2"><i class="bi bi-truck me-1"></i>Shipping calculated at checkout. Funds are released to the seller only after you confirm delivery.</div>
                        @else
                            <button class="btn btn-secondary w-100 btn-lg mb-2" disabled>Out of stock</button>
                        @endif
                        @if($product->vendor->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $product->vendor->whatsapp) }}?text={{ urlencode('Hi, I\'m interested in: ' . $product->name) }}"
                               target="_blank" rel="noopener" class="btn btn-outline-success w-100">
                                <i class="bi bi-whatsapp me-1"></i>Ask the Seller
                            </a>
                        @endif
                    @elseif($hasPurchased)
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
