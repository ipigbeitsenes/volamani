@extends('layouts.app')

@section('title', 'Product Requests — Volamani')

@section('content')
<div class="container-fluid py-4">
    <div class="row">

        {{-- Sidebar --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Filter Requests</div>
                <div class="card-body">
                    <form action="{{ route('marketplace.requests.index') }}" method="GET" id="filterForm">
                        <div class="mb-3">
                            <input type="text" name="q" class="form-control"
                                placeholder="Search requests..."
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
                            <label class="form-label fw-semibold small text-uppercase text-muted">Max Budget ({{ currency_symbol() }})</label>
                            <input type="number" name="budget_max" class="form-control form-control-sm"
                                placeholder="e.g. 50000"
                                value="{{ $filters['budget_max'] ?? '' }}" min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Deadline</label>
                            @foreach([3 => 'Within 3 days', 7 => 'Within a week', 30 => 'This month'] as $days => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="deadline"
                                        id="dl_{{ $days }}" value="{{ $days }}"
                                        {{ ($filters['deadline'] ?? '') == $days ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <label class="form-check-label small" for="dl_{{ $days }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">Apply</button>
                        <a href="{{ route('marketplace.requests.index') }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear All</a>
                    </form>
                </div>
            </div>

            @auth
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body text-center">
                        <i class="bi bi-megaphone fs-2 text-primary mb-2 d-block"></i>
                        <p class="small text-muted mb-3">Need something built? Post a request and get quotes from vendors.</p>
                        <a href="{{ route('requests.create') }}" class="btn btn-primary w-100">
                            Post a Request
                        </a>
                    </div>
                </div>
            @endauth
        </div>

        {{-- Requests List --}}
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0 text-muted small">
                    {{ $requests->total() }} open request{{ $requests->total() !== 1 ? 's' : '' }}
                </p>
                <div class="d-flex gap-2 align-items-center">
                    @auth
                        <a href="{{ route('requests.my') }}" class="btn btn-outline-secondary btn-sm">
                            My Requests
                        </a>
                    @endauth
                    <select name="sort" form="filterForm" class="form-select form-select-sm w-auto"
                        onchange="document.getElementById('filterForm').submit()">
                        <option value="latest" {{ ($filters['sort'] ?? 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                        <option value="deadline" {{ ($filters['sort'] ?? '') === 'deadline' ? 'selected' : '' }}>Soonest Deadline</option>
                        <option value="budget_high" {{ ($filters['sort'] ?? '') === 'budget_high' ? 'selected' : '' }}>Highest Budget</option>
                        <option value="most_bids" {{ ($filters['sort'] ?? '') === 'most_bids' ? 'selected' : '' }}>Most Bids</option>
                    </select>
                </div>
            </div>

            @if($requests->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">No open requests found.</p>
                    @auth
                        <a href="{{ route('requests.create') }}" class="btn btn-primary">Post the First Request</a>
                    @endauth
                </div>
            @else
                @foreach($requests as $req)
                    <div class="card border-0 shadow-sm mb-3 request-card">
                        <div class="card-body">
                            <div class="row align-items-start">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-start gap-2 mb-1">
                                        <span class="badge bg-{{ $req->status->badge() }}">{{ $req->status->label() }}</span>
                                        @if($req->category)
                                            <span class="badge bg-light text-dark border">{{ $req->category->name }}</span>
                                        @endif
                                        @if($req->isExpired())
                                            <span class="badge bg-warning text-dark">Deadline Passed</span>
                                        @endif
                                    </div>
                                    <h6 class="fw-bold mb-1">
                                        <a href="{{ route('marketplace.requests.show', $req->id) }}"
                                           class="text-dark text-decoration-none">
                                            {{ $req->title }}
                                        </a>
                                    </h6>
                                    <p class="text-muted small mb-2">{{ Str::limit($req->description, 150) }}</p>
                                    <div class="d-flex gap-3 text-muted small flex-wrap">
                                        @if($req->budget_min || $req->budget_max)
                                            <span><i class="bi bi-cash me-1"></i>{{ $req->budgetRange() }}</span>
                                        @else
                                            <span class="text-muted"><i class="bi bi-cash me-1"></i>Flexible budget</span>
                                        @endif
                                        @if($req->deadline_at)
                                            <span><i class="bi bi-calendar me-1"></i>Deadline: {{ $req->deadline_at->format('M j, Y') }}</span>
                                        @endif
                                        @if($req->location)
                                            <span><i class="bi bi-geo-alt me-1"></i>{{ $req->location }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <div class="mb-2">
                                        <span class="fw-bold text-primary fs-5">
                                            {{ $req->quotations_count }}
                                        </span>
                                        <span class="text-muted small"> bid{{ $req->quotations_count !== 1 ? 's' : '' }}</span>
                                    </div>
                                    <div class="text-muted small mb-3">
                                        Posted {{ $req->created_at->diffForHumans() }}
                                    </div>
                                    <a href="{{ route('marketplace.requests.show', $req->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        View Request
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="mt-3">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
