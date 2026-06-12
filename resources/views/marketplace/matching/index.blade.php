@extends('layouts.app')

@section('title', 'Business Matching')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Business Matching</h4>
            <p class="text-muted mb-0">Tell us what you need and we'll match you with the right vendors.</p>
        </div>
        <a href="{{ route('matching.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New brief</a>
    </div>

    @if($requests->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-diagram-3 display-5 text-primary"></i>
                <h5 class="fw-bold mt-3">No briefs yet</h5>
                <p class="text-muted">Post a brief describing what you're looking for and get matched with vetted vendors.</p>
                <a href="{{ route('matching.create') }}" class="btn btn-primary">Create your first brief</a>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($requests as $req)
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0"><a href="{{ route('matching.show', $req) }}" class="text-decoration-none">{{ $req->title }}</a></h6>
                                <span class="badge bg-{{ $req->status->badge() }}">{{ $req->status->label() }}</span>
                            </div>
                            <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($req->description, 110) }}</p>
                            <div class="d-flex flex-wrap gap-2 small text-muted">
                                <span><i class="bi {{ $req->looking_for->icon() }} me-1"></i>{{ $req->looking_for->label() }}</span>
                                @if($req->category)<span><i class="bi bi-tag me-1"></i>{{ $req->category }}</span>@endif
                                <span><i class="bi bi-cash me-1"></i>{{ $req->budgetLabel() }}</span>
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <span class="small text-muted">{{ $req->matches_count }} match{{ $req->matches_count === 1 ? '' : 'es' }}</span>
                            <a href="{{ route('matching.show', $req) }}" class="btn btn-sm btn-outline-primary">View matches</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
