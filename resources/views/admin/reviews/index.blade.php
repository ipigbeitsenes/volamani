@extends('layouts.admin')

@section('title', 'Review Moderation')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4">Review Moderation</h4>

    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }}">{{ session($key) }}</div>@endif
    @endforeach

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search title or body...">
        </div>
        <div class="col-md-2">
            <select name="rating" class="form-select">
                <option value="">Any rating</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected(($filters['rating'] ?? '') == $i)>{{ $i }} star</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <select name="approved" class="form-select">
                <option value="">All</option>
                <option value="1" @selected(($filters['approved'] ?? '') === '1')>Visible</option>
                <option value="0" @selected(($filters['approved'] ?? '') === '0')>Hidden</option>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($reviews->isEmpty())
                <p class="text-muted text-center py-5 mb-0">No reviews found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reviewer</th>
                                <th>Target</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $review)
                                <tr>
                                    <td>{{ $review->reviewer->name ?? '—' }}</td>
                                    <td class="small text-muted">{{ class_basename($review->reviewable_type) }} #{{ $review->reviewable_id }}</td>
                                    <td>@include('reviews._stars', ['rating' => $review->rating])</td>
                                    <td class="small" style="max-width:280px;">
                                        @if($review->title)<div class="fw-semibold">{{ $review->title }}</div>@endif
                                        <div class="text-truncate">{{ $review->body }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $review->is_approved ? 'success' : 'secondary' }}-subtle text-{{ $review->is_approved ? 'success' : 'secondary' }}">
                                            {{ $review->is_approved ? 'Visible' : 'Hidden' }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if($review->is_approved)
                                            <form action="{{ route('admin.reviews.hide', $review) }}" method="POST" class="d-inline">@csrf
                                                <button class="btn btn-sm btn-outline-warning">Hide</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="d-inline">@csrf
                                                <button class="btn btn-sm btn-outline-success">Approve</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this review permanently?');">@csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">{{ $reviews->withQueryString()->links() }}</div>
</div>
@endsection
