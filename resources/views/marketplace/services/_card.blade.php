<div class="card h-100 border-0 shadow-sm">
    <a href="{{ route('marketplace.services.show', $service->slug) }}">
        <img src="{{ $service->thumbnail_url }}"
             alt="{{ $service->title }}"
             class="card-img-top"
             style="height: 180px; object-fit: cover;">
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

        @if($service->average_rating > 0)
            <div class="mb-2">
                <span class="text-warning small">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= round($service->average_rating) ? '-fill' : '' }}"></i>
                    @endfor
                </span>
                <span class="text-muted small">({{ $service->reviews_count }})</span>
            </div>
        @endif

        <div class="mt-auto d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Starting at
            </div>
            <span class="fw-bold text-primary">{{ money($service->lowestPrice()) }}</span>
        </div>
    </div>
</div>
