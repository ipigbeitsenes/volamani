@extends('layouts.vendor')

@section('title', 'Reviews')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-1">Customer Reviews</h4>
    <p class="text-muted mb-4">Reviews across your products, services, and consultations.</p>

    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Average Rating</p>
                    <h4 class="fw-bold mb-0">{{ number_format($vendor->average_rating, 2) }} <span class="fs-6 text-warning">★</span></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Reviews</p>
                    <h4 class="fw-bold mb-0">{{ $vendor->reviews_count }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Trust</p>
                    @include('trust._badge', ['vendor' => $vendor])
                    <div class="small text-muted mt-1">{{ $vendor->trust_score }}/100</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @forelse($reviews as $review)
                <div class="border-bottom py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="fw-semibold">{{ $review->reviewer->name ?? 'User' }}</span>
                            @include('reviews._stars', ['rating' => $review->rating])
                            <div class="text-muted small">on {{ class_basename($review->reviewable_type) }} · {{ $review->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @if($review->title)<div class="fw-semibold mt-2">{{ $review->title }}</div>@endif
                    @if($review->body)<p class="mb-1">{{ $review->body }}</p>@endif

                    @if($review->hasResponse())
                        <div class="bg-light rounded p-2 mt-2 small">
                            <span class="fw-semibold">Your response</span>
                            <div>{{ $review->response }}</div>
                        </div>
                    @else
                        <form action="{{ route('vendor.reviews.respond', $review) }}" method="POST" class="mt-2">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="text" name="response" class="form-control" placeholder="Respond publicly…" maxlength="1000" required>
                                <button class="btn btn-outline-primary">Reply</button>
                            </div>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-muted text-center py-5 mb-0">No reviews yet.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-3">{{ $reviews->links() }}</div>
</div>
@endsection
