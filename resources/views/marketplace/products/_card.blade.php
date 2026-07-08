<div class="card h-100 border-0 shadow-sm product-card">
    <a href="{{ route('marketplace.products.show', $product->slug) }}">
        <img src="{{ $product->thumbnail_url }}"
             alt="{{ $product->name }}"
             class="card-img-top bg-light"
             style="height: 180px; object-fit: contain;">
    </a>

    @if($product->hasDiscount())
        <span class="badge bg-danger position-absolute top-0 end-0 m-2">
            -{{ $product->discountPercent() }}%
        </span>
    @endif

    @if($product->isPhysical())
        <span class="badge bg-{{ $product->kind->badge() }} position-absolute top-0 start-0 m-2">
            <i class="bi {{ $product->kind->icon() }} me-1"></i>Physical
        </span>
    @endif

    @if($product->is_featured)
        <span class="badge bg-warning text-dark position-absolute bottom-0 start-0 m-2">
            <i class="bi bi-star-fill me-1"></i>Featured
        </span>
    @endif

    <div class="card-body d-flex flex-column">
        <p class="text-muted small mb-1">
            <a href="{{ route('storefront.show', $product->vendor->user->username) }}"
               class="text-muted text-decoration-none">
                {{ $product->vendor->business_name }}
            </a>
        </p>

        <h6 class="card-title mb-1">
            <a href="{{ route('marketplace.products.show', $product->slug) }}"
               class="text-dark text-decoration-none">
                {{ Str::limit($product->name, 60) }}
            </a>
        </h6>

        <div class="mb-2 d-flex align-items-center justify-content-between gap-2">
            <x-rating-stars :rating="$product->average_rating" :count="$product->reviews_count" size="sm" />
            <x-share :url="route('marketplace.products.show', $product->slug)"
                     :title="$product->name" size="sm" :label="false" :copy-button="false" />
        </div>

        <div class="mt-auto d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold text-primary">{{ money($product->price) }}</span>
                @if($product->hasDiscount())
                    <small class="text-muted text-decoration-line-through ms-1">
                        {{ money($product->compare_price) }}
                    </small>
                @endif
            </div>
            @if($product->isPhysical())
                <span class="badge {{ $product->inStock() ? 'bg-success' : 'bg-secondary' }} small">
                    {{ $product->inStock() ? 'In stock' : 'Out of stock' }}
                </span>
            @else
                <span class="badge bg-light text-dark border small">
                    {{ $product->type->label() }}
                </span>
            @endif
        </div>

        <div class="mt-2 d-grid gap-2">
            <a href="{{ route('marketplace.products.show', $product->slug) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye me-1"></i>View Details
            </a>
            @if($product->isDigital())
                <form method="POST" action="{{ route('cart.products.add', $product->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                    </button>
                </form>
            @elseif($product->inStock())
                {{-- Physical: variant products bounce to the page to choose an option --}}
                <form method="POST" action="{{ route('cart.physical.add', $product->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
