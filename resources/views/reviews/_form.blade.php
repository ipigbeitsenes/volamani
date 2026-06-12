{{-- Review submission form. Expects $type (product|service|consultant) and $reviewableId. --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Write a review</h6>
        <form action="{{ route('reviews.store') }}" method="POST">
            @csrf
            <input type="hidden" name="reviewable_type" value="{{ $type }}">
            <input type="hidden" name="reviewable_id" value="{{ $reviewableId }}">

            <div class="mb-2">
                <label class="form-label small fw-semibold d-block">Your rating</label>
                <div class="star-input fs-4 text-warning" data-rating-input>
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star" data-star="{{ $i }}" role="button"></i>
                    @endfor
                </div>
                <input type="hidden" name="rating" value="{{ old('rating') }}" required>
                @error('rating')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="mb-2">
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="Title (optional)" value="{{ old('title') }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <textarea name="body" rows="3" class="form-control @error('body') is-invalid @enderror" placeholder="Share your experience…">{{ old('body') }}</textarea>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button class="btn btn-primary btn-sm">Submit Review</button>
        </form>
    </div>
</div>

<script>
    (function () {
        const wrap = document.querySelector('[data-rating-input]');
        if (!wrap) return;
        const input = wrap.parentElement.querySelector('input[name="rating"]');
        const stars = wrap.querySelectorAll('[data-star]');
        function paint(val) {
            stars.forEach(s => s.classList.toggle('bi-star-fill', s.dataset.star <= val) || s.classList.toggle('bi-star', s.dataset.star > val));
        }
        stars.forEach(s => {
            s.addEventListener('mouseenter', () => paint(s.dataset.star));
            s.addEventListener('click', () => { input.value = s.dataset.star; paint(s.dataset.star); });
        });
        wrap.addEventListener('mouseleave', () => paint(input.value || 0));
        paint(input.value || 0);
    })();
</script>
