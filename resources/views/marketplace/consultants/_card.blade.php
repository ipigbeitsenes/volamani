<div class="col-md-6 col-xl-4">
    <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-3">
                @if ($consultant->getAvatarUrlAttribute())
                    <img src="{{ $consultant->getAvatarUrlAttribute() }}" alt="{{ $consultant->display_name }}"
                        class="rounded-circle" style="width:52px;height:52px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:52px;height:52px;font-size:1.4rem;">
                        {{ strtoupper(substr($consultant->display_name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h6 class="mb-0">{{ $consultant->display_name }}</h6>
                    <small class="text-muted">{{ $consultant->niche }}</small>
                </div>
            </div>

            <p class="small text-muted mb-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                {{ $consultant->bio }}
            </p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-light text-dark border">
                    <i class="bi bi-star-fill text-warning me-1"></i>{{ number_format($consultant->average_rating, 1) }}
                </span>
                <span class="badge bg-light text-dark border">
                    {{ $consultant->experience_years }}y exp
                </span>
                <span class="badge bg-light text-dark border">
                    {{ number_format($consultant->total_sessions) }} sessions
                </span>
            </div>

            @if ($consultant->packages->isNotEmpty())
                <p class="small mb-3">
                    From <strong class="text-success">{{ money($consultant->lowestPrice()) }}</strong>
                </p>
            @endif

            <a href="{{ route('marketplace.consultants.show', $consultant->slug) }}"
               class="btn btn-outline-primary btn-sm w-100">View Profile</a>
        </div>
    </div>
</div>
