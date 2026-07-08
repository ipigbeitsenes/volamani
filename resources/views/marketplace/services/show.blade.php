@extends('layouts.app')

@php
    $seoName = $service->seo_title ?: $service->title;
    $seoDesc = \Illuminate\Support\Str::limit(strip_tags($service->seo_description ?: ($service->short_description ?: $service->description)), 155);
    // Only the service's own uploaded (raster) image — skip the SVG placeholder.
    $seoImg  = $service->thumbnail ? $service->thumbnail_url : null;
    if ($seoImg && ! \Illuminate\Support\Str::startsWith($seoImg, ['http://', 'https://', '//', 'data:'])) {
        $seoImg = url($seoImg);
    }
    $seoReviewCount = (int) ($service->reviews_count ?? 0);
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
    'name'     => $service->title,
    'description' => $seoDesc,
    'image'    => $seoImg,
    'sku'      => 'service-' . $service->id,
    'category' => $service->category?->name,
    'brand'    => $service->vendor ? ['@type' => 'Brand', 'name' => $service->vendor->business_name] : null,
    'offers'   => [
        '@type'         => 'Offer',
        'url'           => route('marketplace.services.show', $service->slug),
        'priceCurrency' => 'NGN',
        'price'         => number_format($service->lowestPrice() / 100, 2, '.', ''),
        'availability'  => 'https://schema.org/InStock',
        'seller'        => $service->vendor ? ['@type' => 'Organization', 'name' => $service->vendor->business_name] : null,
    ],
    'aggregateRating' => ($service->average_rating > 0 && $seoReviewCount > 0) ? [
        '@type'       => 'AggregateRating',
        'ratingValue' => number_format($service->average_rating, 1),
        'reviewCount' => $seoReviewCount,
    ] : null,
], fn ($v) => $v !== null), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('marketplace.services.index') }}">Services</a></li>
            @if($service->category)
                <li class="breadcrumb-item">
                    <a href="{{ route('marketplace.services.index', ['category' => $service->category_id]) }}">
                        {{ $service->category->name }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active">{{ Str::limit($service->title, 50) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Left: Service Details --}}
        <div class="col-lg-7">
            <h1 class="h3 fw-bold mb-2">{{ $service->title }}</h1>

            {{-- Vendor & stats --}}
            <div class="d-flex align-items-center gap-3 mb-3">
                <a href="{{ route('storefront.show', $service->vendor->user->username) }}"
                   class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                    <img src="{{ $service->vendor->logo_url }}"
                         class="rounded-circle"
                         style="width:36px;height:36px;object-fit:cover;"
                         alt="{{ $service->vendor->business_name }}">
                    <span class="fw-semibold small">{{ $service->vendor->business_name }}</span>
                </a>
                <x-rating-stars :rating="$service->average_rating" :count="$service->reviews_count" size="md" />
                <span class="text-muted small">
                    <i class="bi bi-bag-check me-1"></i>{{ number_format($service->orders_count) }} orders
                </span>
            </div>

            {{-- Share / copy link --}}
            <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                <span class="text-muted small fw-semibold me-1">Share:</span>
                <x-share :url="route('marketplace.services.show', $service->slug)"
                         :title="$service->title" size="sm" :label="false" />
            </div>

            {{-- Thumbnail --}}
            <div class="mb-4 rounded overflow-hidden bg-light d-flex align-items-center justify-content-center" style="height: 380px;">
                <img src="{{ $service->thumbnail_url }}"
                     alt="{{ $service->title }}"
                     class="img-fluid mw-100"
                     style="max-height: 380px; object-fit: contain;">
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <h5 class="fw-bold">About This Service</h5>
                <div class="text-secondary lh-lg">
                    {!! nl2br(e($service->description)) !!}
                </div>
            </div>

            {{-- FAQs --}}
            @if($service->faqs->count() > 0)
                <div class="mb-4">
                    <h5 class="fw-bold mb-3">Frequently Asked Questions</h5>
                    <div class="accordion" id="faqAccordion">
                        @foreach($service->faqs as $faq)
                            <div class="accordion-item border-0 border-bottom">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-transparent fw-semibold"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#faq{{ $faq->id }}">
                                        {{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="faq{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">{{ $faq->answer }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Reviews --}}
            @if($reviews->count() > 0)
                <div class="mb-4">
                    <h5 class="fw-bold mb-3">Reviews</h5>
                    @foreach($reviews as $review)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ $review->reviewer->getAvatarUrlAttribute() ?? 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}"
                                     class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                <div>
                                    <div class="fw-semibold small">{{ $review->reviewer->name }}</div>
                                    <div class="text-warning" style="font-size:0.75rem;">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <span class="ms-auto text-muted small">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if($review->body)
                                <p class="mb-0 small text-secondary">{{ $review->body }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @auth
                @if($service->canBeReviewedBy(auth()->user()))
                    @include('reviews._form', ['type' => 'service', 'reviewableId' => $service->id])
                @endif
            @endauth
        </div>

        {{-- Right: Package Selector --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow sticky-top" style="top: 1rem;">
                <div class="card-header bg-white border-0 pb-0">
                    {{-- Package tabs --}}
                    <ul class="nav nav-tabs border-0" id="packageTabs">
                        @foreach($service->packages as $package)
                            <li class="nav-item">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }} fw-semibold"
                                    data-bs-toggle="tab"
                                    data-bs-target="#pkg{{ $package->id }}">
                                    {{ $package->tier->label() }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        @foreach($service->packages as $package)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pkg{{ $package->id }}">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <div class="fw-bold">{{ $package->name }}</div>
                                        <div class="text-muted small">{{ $package->description }}</div>
                                    </div>
                                    <div class="fs-4 fw-bold text-primary text-nowrap ms-3">
                                        {{ money($package->price) }}
                                    </div>
                                </div>

                                <div class="d-flex gap-3 mb-3 text-muted small">
                                    <span><i class="bi bi-clock me-1"></i>{{ $package->delivery_days }}-day delivery</span>
                                    <span><i class="bi bi-arrow-repeat me-1"></i>{{ $package->revisionsLabel() }} revisions</span>
                                </div>

                                @if($package->features)
                                    <ul class="list-unstyled mb-3 small">
                                        @foreach($package->features as $feature)
                                            @if(trim($feature))
                                                <li class="mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>{{ trim($feature) }}
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif

                                @if(auth()->check())
                                    <form method="POST" action="{{ route('marketplace.services.order', $service->slug) }}">
                                        @csrf
                                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                                        <button type="submit" class="btn btn-primary w-100">
                                            Order Now ({{ money($package->price) }})
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}"
                                       class="btn btn-primary w-100">
                                        Login to Order
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('cart.services.add', $package->id) }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary w-100 btn-sm">
                                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Vendor Card --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <a href="{{ route('storefront.show', $service->vendor->user->username) }}"
                       class="d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <img src="{{ $service->vendor->logo_url }}"
                             class="rounded-circle"
                             style="width:52px;height:52px;object-fit:cover;">
                        <div>
                            <div class="fw-bold">{{ $service->vendor->business_name }}</div>
                            @if($service->vendor->tagline)
                                <div class="text-muted small">{{ $service->vendor->tagline }}</div>
                            @endif
                            <div class="text-muted small">
                                <i class="bi bi-people me-1"></i>{{ number_format($service->vendor->followers_count) }} followers
                            </div>
                        </div>
                    </a>
                    <div class="ms-auto">
                        @include('social._follow_button', ['vendor' => $service->vendor])
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Related Services --}}
    @if($related->count() > 0)
        <div class="mt-5">
            <h4 class="fw-bold mb-3">Related Services</h4>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                @foreach($related as $relService)
                    <div class="col">
                        @include('marketplace.services._card', ['service' => $relService])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
