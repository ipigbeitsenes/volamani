@extends('layouts.app')

@section('title', $vendor->business_name . ' — Storefront')
@section('meta_description', $vendor->tagline ?? 'Shop from ' . $vendor->business_name . ' on Volamani')

@section('content')

{{-- Banner --}}
<div class="position-relative mb-0" style="background: #0f172a; min-height: 220px; overflow:hidden;">
    @if($vendor->banner_url)
        <img src="{{ $vendor->banner_url }}" alt="Banner"
             class="w-100 object-fit-cover position-absolute top-0 start-0 h-100 opacity-50"
             style="object-fit:cover;">
    @endif
    <div class="container position-relative py-5">
        <div class="d-flex align-items-end gap-4">
            <img src="{{ $vendor->logo_url }}" class="rounded-3 border border-3 border-white shadow"
                 width="96" height="96" style="object-fit:cover" alt="{{ $vendor->business_name }}">
            <div class="pb-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h2 class="fw-bold text-white mb-0">{{ $vendor->business_name }}</h2>
                    @if($vendor->isVerified())
                        <span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                    @endif
                    @if($vendor->is_featured)
                        <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Featured</span>
                    @endif
                </div>
                @if($vendor->tagline)
                    <p class="text-white-50 mb-1 mt-1">{{ $vendor->tagline }}</p>
                @endif
                <div class="d-flex flex-wrap gap-3 text-white-50 small mt-2">
                    @if($vendor->category)
                        <span><i class="bi bi-tag me-1"></i>{{ $vendor->category }}</span>
                    @endif
                    @if($vendor->city || $vendor->state)
                        <span><i class="bi bi-geo-alt me-1"></i>{{ collect([$vendor->city, $vendor->state])->filter()->join(', ') }}</span>
                    @endif
                    <span><i class="bi bi-eye me-1"></i>{{ number_format($vendor->views_count) }} views</span>
                    <span><i class="bi bi-people me-1"></i>{{ number_format($vendor->followers_count) }} followers</span>
                    @if($vendor->totalReviews() > 0)
                        <span><i class="bi bi-star-fill text-warning me-1"></i>{{ number_format($vendor->averageRating(), 1) }} ({{ $vendor->totalReviews() }} reviews)</span>
                    @endif
                    <span>@include('trust._badge', ['vendor' => $vendor])</span>
                </div>
            </div>
            <div class="ms-auto d-flex gap-2 pb-1">
                @include('social._follow_button', ['vendor' => $vendor])
                @if($vendor->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $vendor->whatsapp) }}" target="_blank"
                       class="btn btn-success btn-sm fw-semibold">
                        <i class="bi bi-whatsapp me-1"></i>WhatsApp
                    </a>
                @endif
                @if($vendor->website)
                    <a href="{{ $vendor->website }}" target="_blank" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-globe me-1"></i>Website
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">

        {{-- Main content --}}
        <div class="col-lg-9">

            {{-- Nav tabs --}}
            <ul class="nav nav-tabs mb-4" id="storefrontTabs">
                @if($products->count())
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#products">
                            <i class="bi bi-box-seam me-1"></i>Products
                            <span class="badge bg-secondary ms-1">{{ $products->count() }}</span>
                        </a>
                    </li>
                @endif
                @if($services->count())
                    <li class="nav-item">
                        <a class="nav-link {{ $products->isEmpty() ? 'active' : '' }}" data-bs-toggle="tab" href="#services">
                            <i class="bi bi-briefcase me-1"></i>Services
                            <span class="badge bg-secondary ms-1">{{ $services->count() }}</span>
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ $products->isEmpty() && $services->isEmpty() ? 'active' : '' }}" data-bs-toggle="tab" href="#about">
                        <i class="bi bi-info-circle me-1"></i>About
                    </a>
                </li>
                @if($recentReviews->count())
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#reviews">
                            <i class="bi bi-star me-1"></i>Reviews
                            <span class="badge bg-secondary ms-1">{{ $vendor->totalReviews() }}</span>
                        </a>
                    </li>
                @endif
            </ul>

            <div class="tab-content">

                {{-- Products tab --}}
                @if($products->count())
                <div class="tab-pane fade show active" id="products">
                    <div class="row g-3">
                        @foreach($products as $product)
                        <div class="col-6 col-md-4">
                            <a href="{{ route('marketplace.products.show', $product->slug) }}" class="card text-decoration-none border-0 shadow-sm h-100">
                                <img src="{{ $product->thumbnail_url }}"
                                     class="card-img-top" style="height:160px;object-fit:cover" alt="{{ $product->name }}">
                                <div class="card-body p-3">
                                    <h6 class="fw-semibold text-dark mb-1 text-truncate">{{ $product->name }}</h6>
                                    <div class="fw-bold text-primary">{{ money($product->price) }}</div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Services tab --}}
                @if($services->count())
                <div class="tab-pane fade {{ $products->isEmpty() ? 'show active' : '' }}" id="services">
                    <div class="row g-3">
                        @foreach($services as $service)
                        <div class="col-md-6">
                            <a href="{{ route('marketplace.services.show', $service->slug) }}" class="card text-decoration-none border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <h6 class="fw-semibold text-dark mb-1">{{ $service->title }}</h6>
                                    <p class="text-muted small mb-2 text-truncate">{{ $service->description }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary">From {{ money($service->lowestPrice()) }}</span>
                                        <span class="small text-muted"><i class="bi bi-clock me-1"></i>{{ $service->delivery_days }} days</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- About tab --}}
                <div class="tab-pane fade {{ $products->isEmpty() && $services->isEmpty() ? 'show active' : '' }}" id="about">
                    @if($vendor->description)
                        <div class="mb-4">
                            <h6 class="fw-bold">About {{ $vendor->business_name }}</h6>
                            <p class="text-muted" style="white-space:pre-line">{{ $vendor->description }}</p>
                        </div>
                    @else
                        <p class="text-muted">This vendor hasn't added a description yet.</p>
                    @endif

                    @if($vendor->address || $vendor->city || $vendor->state)
                    <div class="mb-4">
                        <h6 class="fw-bold">Location</h6>
                        <p class="text-muted mb-0">
                            <i class="bi bi-geo-alt text-primary me-1"></i>
                            {{ collect([$vendor->address, $vendor->city, $vendor->state, 'Nigeria'])->filter()->join(', ') }}
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Reviews tab --}}
                @if($recentReviews->count())
                <div class="tab-pane fade" id="reviews">
                    @foreach($recentReviews as $review)
                    <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
                        <img src="{{ $review->user->avatar_url }}" class="rounded-circle flex-shrink-0"
                             width="40" height="40" alt="">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-semibold small">{{ $review->user->name }}</span>
                                <div>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill text-warning' : ' text-muted' }} small"></i>
                                    @endfor
                                </div>
                                <span class="text-muted small">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mb-0 small text-muted">{{ $review->comment }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3">Contact Vendor</h6>
                    @if($vendor->whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $vendor->whatsapp) }}?text=Hi, I found you on Volamani" target="_blank"
                           class="btn btn-success w-100 mb-2 fw-semibold">
                            <i class="bi bi-whatsapp me-2"></i>Chat on WhatsApp
                        </a>
                    @endif
                    @if($vendor->website)
                        <a href="{{ $vendor->website }}" target="_blank" class="btn btn-outline-secondary w-100 btn-sm">
                            <i class="bi bi-globe me-1"></i>Visit Website
                        </a>
                    @endif
                    @if(! $vendor->whatsapp && ! $vendor->website)
                        <p class="text-muted small mb-0">No contact info provided.</p>
                    @endif
                </div>
            </div>

            @if(collect($vendor->social_links ?? [])->filter()->count())
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3">Follow Us</h6>
                    @foreach([['facebook','bi-facebook text-primary'],['twitter','bi-twitter-x'],['instagram','bi-instagram text-danger'],['linkedin','bi-linkedin text-primary'],['youtube','bi-youtube text-danger']] as $s)
                        @if(!empty($vendor->social_links[$s[0]]))
                            <a href="{{ $vendor->social_links[$s[0]] }}" target="_blank"
                               class="btn btn-outline-secondary btn-sm me-1 mb-1">
                                <i class="bi {{ $s[1] }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Member since</span>
                        <span class="fw-medium">{{ $vendor->created_at->format('M Y') }}</span>
                    </div>
                    @if($vendor->totalReviews() > 0)
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Rating</span>
                        <span class="fw-medium">
                            <i class="bi bi-star-fill text-warning small me-1"></i>{{ number_format($vendor->averageRating(), 1) }}/5.0
                        </span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Total Reviews</span>
                        <span class="fw-medium">{{ $vendor->totalReviews() }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
