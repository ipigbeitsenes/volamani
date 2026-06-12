@extends('layouts.vendor')

@section('title', 'Edit Service')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('vendor.services.index') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i> Back to Services
        </a>
        <h4 class="fw-bold mb-0 mt-1">Edit: {{ Str::limit($service->title, 50) }}</h4>
    </div>
    <span class="badge bg-{{ $service->status->badge() }} fs-6">{{ $service->status->label() }}</span>
</div>

@if($service->status->value === 'rejected' && $service->rejection_reason)
    <div class="alert alert-danger mb-4">
        <strong>Rejection Reason:</strong> {{ $service->rejection_reason }}
    </div>
@endif

<form action="{{ route('vendor.services.update', $service->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('vendor.services._form')
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i> Save Changes
        </button>
        <a href="{{ route('vendor.services.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
