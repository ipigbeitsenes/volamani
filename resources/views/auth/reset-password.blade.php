@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <h4 class="fw-bold mb-1">Set new password</h4>
    <p class="text-muted mb-4 small">Choose a strong password for your account.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label fw-medium small">Email Address</label>
            <input type="email" name="email" value="{{ old('email', $email) }}"
                   class="form-control @error('email') is-invalid @enderror" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium small">New Password</label>
            <input type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Min 8 characters" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label class="form-label fw-medium small">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            Reset Password
        </button>
    </form>
@endsection
