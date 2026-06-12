@extends('layouts.vendor')

@section('title', 'Create Consultant Profile')

@section('content')
<div class="container py-4" style="max-width:700px">
    <h4 class="mb-1">Create Your Consultant Profile</h4>
    <p class="text-muted mb-4">Tell potential clients about your expertise and background.</p>

    <form method="POST" action="{{ route('vendor.consultations.setup.store') }}">
        @csrf
        @include('vendor.consultations._profile_form')
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg">Create Profile & Add Packages</button>
        </div>
    </form>
</div>
@endsection
