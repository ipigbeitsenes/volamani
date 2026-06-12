@extends('layouts.app')

@section('title', $consultant->display_name . ' — Consultant')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        {{-- Main info --}}
        <div class="col-lg-8">
            {{-- Header --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        @if ($consultant->getAvatarUrlAttribute())
                            <img src="{{ $consultant->getAvatarUrlAttribute() }}" alt="{{ $consultant->display_name }}"
                                class="rounded-circle" style="width:72px;height:72px;object-fit:cover;">
                        @else
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                style="width:72px;height:72px;font-size:2rem;">
                                {{ strtoupper(substr($consultant->display_name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h4 class="mb-0">{{ $consultant->display_name }}</h4>
                                    <p class="text-muted mb-1">{{ $consultant->niche }}</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-star-fill me-1"></i>{{ number_format($consultant->average_rating, 1) }}
                                            ({{ $consultant->reviews_count }} reviews)
                                        </span>
                                        <span class="badge bg-light text-dark border">{{ $consultant->experience_years }} years experience</span>
                                        <span class="badge bg-light text-dark border">{{ number_format($consultant->total_sessions) }} sessions</span>
                                        @if ($consultant->is_available)
                                            <span class="badge bg-success">Available</span>
                                        @else
                                            <span class="badge bg-secondary">Not accepting bookings</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    @if ($consultant->linkedin)
                                        <a href="{{ $consultant->linkedin }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-linkedin"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bio --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">About</h5>
                    <p class="mb-0">{{ $consultant->bio }}</p>
                </div>
            </div>

            {{-- Expertise tags --}}
            @if (!empty($consultant->expertise))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Areas of Expertise</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($consultant->expertise as $skill)
                                <span class="badge bg-primary bg-opacity-10 text-primary border">{{ trim($skill) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Availability schedule --}}
            @if ($consultant->availability->isNotEmpty())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Weekly Availability</h5>
                        <div class="row g-2">
                            @foreach ($consultant->availabilityByDay() as $day)
                                <div class="col-6 col-sm-4">
                                    <div class="p-2 rounded border {{ $day['slot'] ? 'border-success bg-success bg-opacity-5' : 'border-light bg-light' }}">
                                        <div class="small fw-semibold {{ $day['slot'] ? 'text-success' : 'text-muted' }}">{{ $day['label'] }}</div>
                                        @if ($day['slot'])
                                            <div class="small text-muted">{{ $day['slot']->time_range }}</div>
                                        @else
                                            <div class="small text-muted">Unavailable</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Reviews --}}
            @if ($consultant->reviews->isNotEmpty())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Client Reviews</h5>
                        @foreach ($consultant->reviews->take(5) as $review)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong class="small">{{ $review->reviewer->name }}</strong>
                                    <div>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill text-warning' : ' text-muted' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                @if ($review->title)
                                    <p class="mb-1 small fw-semibold">{{ $review->title }}</p>
                                @endif
                                <p class="mb-0 small text-muted">{{ $review->body }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @auth
                @if($consultant->canBeReviewedBy(auth()->user()))
                    @include('reviews._form', ['type' => 'consultant', 'reviewableId' => $consultant->id])
                @endif
            @endauth
        </div>

        {{-- Sidebar: packages & booking --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-body">
                    <h5 class="card-title mb-3">Packages</h5>

                    @forelse ($consultant->packages as $package)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0">{{ $package->name }}</h6>
                                <span class="badge bg-{{ $package->type->value === 'retainer' ? 'info' : 'primary' }} bg-opacity-10 text-{{ $package->type->value === 'retainer' ? 'info' : 'primary' }} border">
                                    {{ $package->type->label() }}
                                </span>
                            </div>
                            <p class="small text-muted mb-2">{{ $package->description }}</p>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="small text-muted"><i class="bi bi-clock me-1"></i>{{ $package->durationLabel() }}</span>
                                @if ($package->type->value === 'retainer' && $package->max_sessions_per_month)
                                    <span class="small text-muted"><i class="bi bi-calendar-check me-1"></i>{{ $package->max_sessions_per_month }}x/month</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-success">{{ money($package->price) }}</strong>
                                @if ($canBook && $consultant->is_available)
                                    <a href="{{ route('consultations.book', $consultant->slug) }}?package={{ $package->id }}"
                                       class="btn btn-primary btn-sm">Book</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small">No packages available yet.</p>
                    @endforelse

                    @if (!auth()->check())
                        <a href="{{ route('login') }}" class="btn btn-primary w-100 mt-2">Login to Book</a>
                    @elseif (!$canBook)
                        <div class="alert alert-info small mb-0 mt-2">This is your own consultation profile.</div>
                    @elseif (!$consultant->is_available)
                        <div class="alert alert-warning small mb-0 mt-2">This consultant is not accepting bookings.</div>
                    @endif

                    @if ($consultant->calendly_url)
                        <hr>
                        <a href="{{ $consultant->calendly_url }}" target="_blank" rel="noopener"
                           class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-calendar3 me-1"></i>Schedule via Calendly
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
