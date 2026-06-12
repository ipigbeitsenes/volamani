{{-- Renders a list of reviews. Expects $reviews (iterable of Review). --}}
@forelse($reviews as $review)
    <div class="border-bottom py-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span class="fw-semibold">{{ $review->reviewer->name ?? 'User' }}</span>
                @if($review->is_verified_purchase)
                    <span class="badge bg-success-subtle text-success ms-1"><i class="bi bi-patch-check-fill"></i> Verified purchase</span>
                @endif
                <div class="mt-1">@include('reviews._stars', ['rating' => $review->rating])</div>
            </div>
            <span class="text-muted small">{{ $review->created_at->diffForHumans() }}</span>
        </div>

        @if($review->title)<div class="fw-semibold mt-2">{{ $review->title }}</div>@endif
        @if($review->body)<p class="mb-1 mt-1">{{ $review->body }}</p>@endif

        @if($review->hasResponse())
            <div class="bg-light rounded p-2 mt-2 small">
                <span class="fw-semibold">Seller response</span>
                <span class="text-muted">· {{ $review->responded_at?->diffForHumans() }}</span>
                <div>{{ $review->response }}</div>
            </div>
        @endif

        @auth
            <form action="{{ route('reviews.helpful', $review) }}" method="POST" class="mt-2">
                @csrf
                <button class="btn btn-sm btn-outline-secondary {{ $review->votedHelpfulBy(auth()->user()) ? 'active' : '' }}">
                    <i class="bi bi-hand-thumbs-up"></i> Helpful ({{ $review->helpful_count }})
                </button>
            </form>
        @endauth
    </div>
@empty
    <p class="text-muted py-3 mb-0">No reviews yet.</p>
@endforelse

@if(isset($reviews) && method_exists($reviews, 'links'))
    <div class="mt-3">{{ $reviews->links() }}</div>
@endif
