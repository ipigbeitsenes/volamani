@extends('layouts.account')

@section('title', 'My Requests — Volamani')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">My Requests</h4>
        <a href="{{ route('requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Post New Request
        </a>
    </div>

    @if($requests->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-megaphone fs-1 text-muted"></i>
                <h5 class="mt-3">No requests yet</h5>
                <p class="text-muted">Post a request and let vendors come to you with their best offers.</p>
                <a href="{{ route('requests.create') }}" class="btn btn-primary">Post Your First Request</a>
            </div>
        </div>
    @else
        @foreach($requests as $req)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <div class="d-flex gap-2 mb-1">
                                <span class="badge bg-{{ $req->status->badge() }}">{{ $req->status->label() }}</span>
                                @if($req->category)
                                    <span class="badge bg-light text-dark border small">{{ $req->category->name }}</span>
                                @endif
                            </div>
                            <h6 class="fw-bold mb-1">
                                <a href="{{ route('marketplace.requests.show', $req->id) }}"
                                   class="text-dark text-decoration-none">{{ $req->title }}</a>
                            </h6>
                            <div class="text-muted small">
                                {{ $req->budgetRange() }}
                                @if($req->deadline_at)
                                    &middot; Deadline: {{ $req->deadline_at->format('M j, Y') }}
                                @endif
                                &middot; Posted {{ $req->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="fw-bold fs-4 text-primary">{{ $req->quotations_count }}</div>
                            <div class="text-muted small">Quotation{{ $req->quotations_count !== 1 ? 's' : '' }}</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ route('marketplace.requests.show', $req->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                {{ $req->quotations_count > 0 ? 'Review Bids' : 'View' }}
                            </a>
                        </div>
                    </div>

                    @if($req->status->value === 'accepted' && $req->acceptedQuotation)
                        <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="small">Accepted:
                                <strong>{{ $req->acceptedQuotation->vendor->business_name }}</strong>
                                — {{ money($req->acceptedQuotation->price) }}, {{ $req->acceptedQuotation->delivery_days }} days
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
