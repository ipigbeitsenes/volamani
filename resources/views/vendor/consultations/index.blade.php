@extends('layouts.vendor')

@section('title', 'Consultation Hub')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4">Consultation Hub</h4>

    @if (!$profile)
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-person-badge fs-1 text-primary mb-3 d-block"></i>
                <h5>Set Up Your Consultant Profile</h5>
                <p class="text-muted mb-4">Create your profile to start offering startup consultations and mentoring sessions.</p>
                <a href="{{ route('vendor.consultations.setup') }}" class="btn btn-primary px-4">Get Started</a>
            </div>
        </div>
    @else
        <div class="row g-4">
            {{-- Quick stats --}}
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Total Sessions</div>
                        <div class="h4 mb-0">{{ number_format($profile->total_sessions) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Average Rating</div>
                        <div class="h4 mb-0">
                            {{ number_format($profile->average_rating, 1) }}
                            <i class="bi bi-star-fill text-warning fs-6"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Active Packages</div>
                        <div class="h4 mb-0">{{ $profile->packages->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Status</div>
                        <div class="h4 mb-0">
                            @if ($profile->is_available)
                                <span class="badge bg-success fs-6">Open</span>
                            @else
                                <span class="badge bg-secondary fs-6">Paused</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick links --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex flex-wrap gap-2">
                        <a href="{{ route('vendor.consultations.profile') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-person-circle me-1"></i>Edit Profile
                        </a>
                        <a href="{{ route('vendor.consultations.packages') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box-seam me-1"></i>Manage Packages
                        </a>
                        <a href="{{ route('vendor.consultations.schedule') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-calendar3 me-1"></i>Set Availability
                        </a>
                        <a href="{{ route('vendor.consultations.sessions') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-list-check me-1"></i>View Sessions
                        </a>
                        <a href="{{ route('marketplace.consultants.show', $profile->slug) }}" target="_blank"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Public Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
