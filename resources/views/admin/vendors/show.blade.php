@extends('layouts.admin')

@section('title', $vendor->business_name)

@section('content')
<div class="container-fluid" style="max-width: 900px;">
    <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-link text-decoration-none mb-3"><i class="bi bi-arrow-left"></i> Back to vendors</a>

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <img src="{{ $vendor->logo_url }}" class="rounded bg-white border" width="64" height="64" style="object-fit:contain;padding:2px" alt="">
            <div class="flex-grow-1">
                <h5 class="fw-bold mb-0">{{ $vendor->business_name }}</h5>
                <div class="text-muted small">{{ $vendor->tagline }}</div>
            </div>
            <span class="badge bg-{{ $vendor->status->badge() }}-subtle text-{{ $vendor->status->badge() }} fs-6">{{ $vendor->status->label() }}</span>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Business details</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Owner</dt><dd class="col-sm-8">{{ $vendor->user->name ?? '—' }} ({{ $vendor->user->email ?? '' }})</dd>
                        <dt class="col-sm-4 text-muted">Category</dt><dd class="col-sm-8">{{ $vendor->category ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Location</dt><dd class="col-sm-8">{{ $vendor->city }} {{ $vendor->state }}</dd>
                        <dt class="col-sm-4 text-muted">Products / Services</dt><dd class="col-sm-8">{{ $vendor->products_count }} / {{ $vendor->services_count }}</dd>
                        <dt class="col-sm-4 text-muted">Commission</dt><dd class="col-sm-8">{{ $vendor->getEffectiveCommissionRate() }}%</dd>
                        <dt class="col-sm-4 text-muted">Description</dt><dd class="col-sm-8">{{ $vendor->description ?? '—' }}</dd>
                        @if($vendor->rejection_reason)
                            <dt class="col-sm-4 text-danger">Last note</dt><dd class="col-sm-8 text-danger">{{ $vendor->rejection_reason }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Decision</div>
                <div class="card-body d-grid gap-2">
                    @if($vendor->status !== \App\Enums\Status::Active)
                        <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}">
                            @csrf
                            <button class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i>Approve store</button>
                        </form>
                    @endif

                    <button class="btn btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#rejectBox">Decline application</button>
                    <div class="collapse" id="rejectBox">
                        <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}" class="mt-2">
                            @csrf
                            <textarea name="reason" class="form-control form-control-sm mb-2" rows="2" placeholder="Reason for declining" required></textarea>
                            <button class="btn btn-sm btn-warning w-100">Confirm decline</button>
                        </form>
                    </div>

                    @if($vendor->status === \App\Enums\Status::Active)
                        <button class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#suspendBox">Suspend store</button>
                        <div class="collapse" id="suspendBox">
                            <form method="POST" action="{{ route('admin.vendors.suspend', $vendor) }}" class="mt-2">
                                @csrf
                                <textarea name="reason" class="form-control form-control-sm mb-2" rows="2" placeholder="Reason for suspension" required></textarea>
                                <button class="btn btn-sm btn-danger w-100">Confirm suspension</button>
                            </form>
                        </div>
                    @endif

                    <a href="{{ $vendor->storefront_url }}" target="_blank" class="btn btn-sm btn-outline-secondary">View storefront</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
