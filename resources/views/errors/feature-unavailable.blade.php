@extends('layouts.app')

@section('title', 'Feature unavailable')
@section('robots', 'noindex, nofollow')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center text-center">
        <div class="col-lg-6">
            <div class="feature-tile mx-auto mb-3 bg-gradient-brand text-white" style="width:64px;height:64px;font-size:1.6rem;">
                <i class="bi bi-slash-circle"></i>
            </div>
            <h1 class="h3 fw-bold mb-2">This feature is currently unavailable</h1>
            <p class="lead-muted mb-4">
                {{ config('features.' . ($featureKey ?? '') . '.0', 'This part of the platform') }}
                has been turned off by the administrators. Please check back later.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Go back</a>
                <a href="{{ route('home') }}" class="btn btn-primary"><i class="bi bi-house me-1"></i>Home</a>
            </div>
        </div>
    </div>
</div>
@endsection
