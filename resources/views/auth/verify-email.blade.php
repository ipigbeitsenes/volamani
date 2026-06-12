@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <div class="text-center mb-4">
        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
             style="width:64px;height:64px">
            <i class="bi bi-envelope-check text-primary fs-3"></i>
        </div>
        <h4 class="fw-bold mb-1">Check your email</h4>
        <p class="text-muted small">
            We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
            Please click the link to activate your account.
        </p>
    </div>

    @if(session('status') === 'verification-link-sent')
        <div class="alert alert-success small text-center">
            <i class="bi bi-check-circle-fill me-1"></i>A new verification link has been sent!
        </div>
    @endif

    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-outline-primary w-100 mb-3">
            <i class="bi bi-arrow-clockwise me-1"></i>Resend Verification Email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link w-100 text-muted small">Sign out</button>
    </form>
@endsection
