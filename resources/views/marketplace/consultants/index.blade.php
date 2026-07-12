@extends('layouts.app')

@section('title', 'Find a Consultant')

@section('content')
<div class="container py-4">
    <div class="row">
        {{-- Sidebar filters --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Filter Consultants</h6>
                    <form method="GET" action="{{ route('marketplace.consultants.index') }}">
                        <div class="mb-3">
                            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                                class="form-control form-control-sm" placeholder="Search consultants…">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Niche</label>
                            <select name="niche" class="form-select form-select-sm">
                                <option value="">All niches</option>
                                @foreach ($niches as $niche)
                                    <option value="{{ $niche }}" @selected(($filters['niche'] ?? '') === $niche)>{{ $niche }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Min. Experience</label>
                            <select name="min_experience" class="form-select form-select-sm">
                                <option value="">Any</option>
                                @foreach ([1,3,5,10] as $yr)
                                    <option value="{{ $yr }}" @selected(($filters['min_experience'] ?? '') == $yr)>{{ $yr }}+ years</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Max. Session Price ({{ currency_symbol() }})</label>
                            <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}"
                                class="form-control form-control-sm" placeholder="e.g. 50000" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Sort by</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="rating" @selected(($filters['sort'] ?? 'rating') === 'rating')>Highest Rated</option>
                                <option value="sessions" @selected(($filters['sort'] ?? '') === 'sessions')>Most Sessions</option>
                                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-sm w-100">Apply Filters</button>
                        @if (array_filter($filters))
                            <a href="{{ route('marketplace.consultants.index') }}" class="btn btn-link btn-sm w-100 mt-1">Clear</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        {{-- Results --}}
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Startup Consultants <span class="text-muted fw-normal fs-6">({{ $consultants->total() }})</span></h5>
            </div>

            @if ($consultants->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-person-x fs-1 d-block mb-3"></i>
                    No consultants match your search.
                </div>
            @else
                <div class="row g-3">
                    @foreach ($consultants as $consultant)
                        @include('marketplace.consultants._card', ['consultant' => $consultant])
                    @endforeach
                </div>
                <div class="mt-4">{{ $consultants->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
