@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <h4 class="fw-bold mb-1">Reset your password</h4>
    <p class="text-muted mb-4 small">Enter your email and we'll send you a reset link.</p>

    @if(session('status'))
        <div class="alert alert-success small">
            <i class="bi bi-check-circle-fill me-1"></i>{{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label class="form-label fw-medium small">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@example.com" autofocus required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            Send Reset Link
        </button>
    </form>

    <p class="text-center small text-muted mt-4 mb-0">
        <a href="{{ route('login') }}" class="text-primary text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Back to Sign In
        </a>
    </p>
@endsection
