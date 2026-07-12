@extends('layouts.app')

@section('title', 'Freelance Services — Volamani')

@section('content')
<div class="container-fluid py-4">
    <div class="row">

        {{-- Sidebar Filters --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Filter Services</div>
                <div class="card-body">
                    <form action="{{ route('marketplace.services.index') }}" method="GET" id="filterForm">
                        <div class="mb-3">
                            <input type="text" name="q" class="form-control"
                                placeholder="Search services..."
                                value="{{ $filters['q'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Category</label>
                            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ ($filters['category'] ?? '') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Delivery Time</label>
                            @foreach([1 => 'Express (1 day)', 3 => 'Up to 3 days', 7 => 'Up to 7 days', 30 => 'Up to 30 days'] as $days => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="delivery"
                                        id="del_{{ $days }}" value="{{ $days }}"
                                        {{ ($filters['delivery'] ?? '') == $days ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <label class="form-check-label small" for="del_{{ $days }}">{{ $label }}</label>
                                </div>
                            @endforeach
                            @if(!empty($filters['delivery']))
                                <a href="{{ request()->fullUrlWithoutQuery(['delivery']) }}" class="small text-muted">Clear</a>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Budget ({{ currency_symbol() }})</label>
                            <input type="number" name="budget" class="form-control form-control-sm"
                                placeholder="Max budget" value="{{ $filters['budget'] ?? '' }}" min="0">
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">Apply</button>
                        <a href="{{ route('marketplace.services.index') }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear All</a>
                    </form>
                </div>
            </div>
        </div>

        {{-- Services Grid --}}
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0 text-muted small">
                    Showing {{ $services->firstItem() ?? 0 }}–{{ $services->lastItem() ?? 0 }}
                    of {{ $services->total() }} services
                </p>
                <select name="sort" form="filterForm" class="form-select form-select-sm w-auto" onchange="document.getElementById('filterForm').submit()">
                    <option value="latest" {{ ($filters['sort'] ?? 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="popular" {{ ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                    <option value="top_rated" {{ ($filters['sort'] ?? '') === 'top_rated' ? 'selected' : '' }}>Top Rated</option>
                </select>
            </div>

            @if($services->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-briefcase fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">No services found. Try adjusting your filters.</p>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @foreach($services as $service)
                        <div class="col">
                            @include('marketplace.services._card', ['service' => $service])
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $services->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
