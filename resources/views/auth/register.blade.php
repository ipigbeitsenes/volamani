@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
    <h4 class="fw-bold mb-1">Create your account</h4>
    <p class="text-muted mb-4 small">Join thousands of African entrepreneurs on Volamani</p>

    <form method="POST" action="{{ route('register.post') }}{{ $referralCode ? '?ref=' . $referralCode : '' }}">
        @csrf

        @if($referralCode)
            <div class="alert alert-success small py-2 mb-3">
                <i class="bi bi-gift-fill me-1"></i>You were referred! Your account will be linked to a referral.
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label fw-medium small">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="John Doe" autofocus required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium small">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@example.com" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium small">Phone Number <span class="text-muted">(optional)</span></label>
            <input type="tel" name="phone" value="{{ old('phone') }}"
                   class="form-control @error('phone') is-invalid @enderror"
                   placeholder="+234 800 000 0000">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium small">I am a</label>
            <select name="user_type" class="form-select @error('user_type') is-invalid @enderror">
                <option value="individual" {{ old('user_type') === 'individual' ? 'selected' : '' }}>Individual</option>
                <option value="freelancer" {{ old('user_type') === 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                <option value="business"   {{ old('user_type') === 'business'   ? 'selected' : '' }}>Business Owner</option>
                <option value="agency"     {{ old('user_type') === 'agency'     ? 'selected' : '' }}>Agency</option>
            </select>
            @error('user_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium small">Password</label>
            <input type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Min 8 characters" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label class="form-label fw-medium small">Confirm Password</label>
            <input type="password" name="password_confirmation"
                   class="form-control"
                   placeholder="Repeat password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            Create Account
        </button>

        <p class="text-center small text-muted mt-3 mb-0">
            By creating an account you agree to our
            <a href="#" class="text-primary text-decoration-none">Terms of Service</a> and
            <a href="#" class="text-primary text-decoration-none">Privacy Policy</a>.
        </p>
    </form>

    <hr class="my-4">

    <p class="text-center small text-muted mb-0">
        Already have an account?
        <a href="{{ route('login') }}" class="text-primary fw-medium text-decoration-none">Sign in</a>
    </p>
@endsection
