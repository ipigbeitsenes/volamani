@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <h4 class="fw-bold mb-1">Welcome back</h4>
    <p class="text-muted mb-4 small">Sign in to your Volamani account</p>

    @if(session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-medium small">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@example.com" autofocus required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-medium small mb-0">Password</label>
                <a href="{{ route('password.request') }}" class="small text-primary text-decoration-none">Forgot password?</a>
            </div>
            <input type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="••••••••" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 d-flex align-items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="form-check-input m-0">
            <label for="remember" class="small text-muted">Keep me signed in</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            Sign In
        </button>
    </form>

    <hr class="my-4">

    <p class="text-center small text-muted mb-0">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-primary fw-medium text-decoration-none">Create account</a>
    </p>
@endsection
