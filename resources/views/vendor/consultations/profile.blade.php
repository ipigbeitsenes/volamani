@extends('layouts.vendor')

@section('title', 'Edit Consultant Profile')

@section('content')
<div class="container py-4" style="max-width:700px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit Consultant Profile</h4>
        <a href="{{ route('marketplace.consultants.show', $profile->slug) }}" target="_blank"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i>Public View
        </a>
    </div>

    <form method="POST" action="{{ route('vendor.consultations.profile.update', $profile) }}">
        @csrf @method('PUT')
        @include('vendor.consultations._profile_form', ['profile' => $profile])
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('vendor.consultations.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
</div>
@endsection
