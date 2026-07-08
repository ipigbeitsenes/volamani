<div class="card h-100 border-0 shadow-sm">
    <a href="{{ route('marketplace.services.show', $service->slug) }}">
        <img src="{{ $service->thumbnail_url }}"
             alt="{{ $service->title }}"
             class="card-img-top bg-light"
             style="height: 180px; object-fit: contain;">
    </a>

    <div class="card-body d-flex flex-column">
        <p class="text-muted small mb-1">
            <a href="{{ route('storefront.show', $service->vendor->user->username) }}"
               class="text-muted text-decoration-none">
                {{ $service->vendor->business_name }}
            </a>
        </p>

        <h6 class="card-title mb-1">
            <a href="{{ route('marketplace.services.show', $service->slug) }}"
               class="text-dark text-decoration-none">
                {{ Str::limit($service->title, 65) }}
            </a>
        </h6>

        <div class="mb-2 d-flex align-items-center justify-content-between gap-2">
            <x-rating-stars :rating="$service->average_rating" :count="$service->reviews_count" size="sm" />
            <x-share :url="route('marketplace.services.show', $service->slug)"
                     :title="$service->title" size="sm" :label="false" :copy-button="false" />
        </div>

        <div class="mt-auto d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Starting at
            </div>
            <span class="fw-bold text-primary">{{ money($service->lowestPrice()) }}</span>
        </div>
    </div>
</div>
