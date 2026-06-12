<div class="card h-100 border-0 shadow-sm product-card">
    <a href="{{ route('marketplace.products.show', $product->slug) }}">
        <img src="{{ $product->thumbnail_url }}"
             alt="{{ $product->name }}"
             class="card-img-top"
             style="height: 180px; object-fit: cover;">
    </a>

    @if($product->hasDiscount())
        <span class="badge bg-danger position-absolute top-0 end-0 m-2">
            -{{ $product->discountPercent() }}%
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

        <div class="mb-2">
            @if($product->average_rating > 0)
                <span class="text-warning small">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= round($product->average_rating) ? '-fill' : '' }}"></i>
                    @endfor
                </span>
                <span class="text-muted small">({{ $product->reviews_count }})</span>
            @endif
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
            <span class="badge bg-light text-dark border small">
                {{ $product->type->label() }}
            </span>
        </div>

        <form method="POST" action="{{ route('cart.products.add', $product->id) }}" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                <i class="bi bi-cart-plus me-1"></i>Add to Cart
            </button>
        </form>
    </div>
</div>
