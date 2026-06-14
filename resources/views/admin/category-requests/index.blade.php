@extends('layouts.admin')

@section('title', 'Category Requests')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Category Requests</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Custom Category Requests</h4>
        <p class="text-muted mb-0 small">Approve to add the category to its domain taxonomy, or reject with a note.</p>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Type</label>
                <select name="domain" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain->value }}" {{ ($filters['domain'] ?? '') === $domain->value ? 'selected' : '' }}>{{ $domain->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Category name...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($requests->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-tags fs-2 d-block mb-2"></i>No category requests found.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Category</th>
                            <th class="small">Type</th>
                            <th class="small">Vendor</th>
                            <th class="small">Status</th>
                            <th class="small">Submitted</th>
                            <th class="small text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $req->name }}</span>
                                @if($req->reason)<div class="text-muted small">{{ $req->reason }}</div>@endif
                            </td>
                            <td><span class="badge bg-{{ $req->domain->badge() }}">{{ $req->domain->label() }}</span></td>
                            <td class="small">{{ $req->vendor?->business_name ?? '—' }}</td>
                            <td><span class="badge bg-{{ $req->status->badge() }}">{{ $req->status->label() }}</span></td>
                            <td class="small text-muted">{{ $req->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                @if($req->isPending())
                                    <form method="POST" action="{{ route('admin.category-requests.approve', $req) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#reject-{{ $req->id }}">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                @else
                                    <span class="small text-muted">
                                        {{ $req->reviewedBy?->name ? 'by ' . $req->reviewedBy->name : 'Reviewed' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @if($req->isPending())
                        <tr class="collapse" id="reject-{{ $req->id }}">
                            <td colspan="6" class="bg-light">
                                <form method="POST" action="{{ route('admin.category-requests.reject', $req) }}" class="row g-2 align-items-end">
                                    @csrf
                                    <div class="col-md-9">
                                        <label class="form-label small text-muted mb-1">Reason for rejection <span class="text-danger">*</span></label>
                                        <input type="text" name="admin_note" class="form-control form-control-sm" maxlength="500" required
                                               placeholder="e.g. Already covered by an existing category">
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-sm btn-danger w-100">Confirm Rejection</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $requests->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
