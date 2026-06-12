@extends('layouts.vendor')

@section('title', 'Create Service')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('vendor.services.index') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i> Back to Services
        </a>
        <h4 class="fw-bold mb-0 mt-1">Create New Service</h4>
    </div>
</div>

<form action="{{ route('vendor.services.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('vendor.services._form')
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-send me-1"></i> Submit for Review
        </button>
        <a href="{{ route('vendor.services.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
