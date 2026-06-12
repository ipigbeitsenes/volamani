@extends('layouts.vendor')

@section('title', 'Add New Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('vendor.products.index') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
        <h4 class="fw-bold mb-0 mt-1">Add New Product</h4>
    </div>
</div>

<form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('vendor.products._form')
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-send me-1"></i> Submit for Review
        </button>
        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
