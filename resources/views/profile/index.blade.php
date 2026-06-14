@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container py-4">
    <div class="row g-4">

        {{-- Sidebar --}}
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mb-3 position-relative d-inline-block mx-auto">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle border border-3 border-primary"
                         width="88" height="88" style="object-fit:cover" alt="{{ $user->name }}">
                    <label for="avatarUpload" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;cursor:pointer">
                        <i class="bi bi-camera small"></i>
                    </label>
                </div>
                <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                @if($user->username)
                    <a href="{{ $user->storefront_url }}" class="small text-primary text-decoration-none">
                        <i class="bi bi-shop me-1"></i>{{ '@' . $user->username }}
                    </a>
                @endif
                <hr>
                <div class="d-flex justify-content-between text-center">
                    <div>
                        <div class="fw-bold">{{ $user->orders()->count() }}</div>
                        <div class="text-muted" style="font-size:.75rem">Orders</div>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $user->referrals()->count() }}</div>
                        <div class="text-muted" style="font-size:.75rem">Referrals</div>
                    </div>
                    <div>
                        <span class="badge bg-{{ $user->kyc_status->badge() }}">
                            {{ $user->kyc_status->label() }}
                        </span>
                        <div class="text-muted" style="font-size:.75rem">KYC</div>
                    </div>
                </div>
            </div>

            {{-- Quick avatar upload --}}
            <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" id="avatarForm">
                @csrf
                <input type="file" id="avatarUpload" name="avatar" class="d-none" accept="image/*"
                       onchange="document.getElementById('avatarForm').submit()">
            </form>

            <div class="card border-0 shadow-sm mt-3">
                <div class="list-group list-group-flush rounded">
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action active">
                        <i class="bi bi-person me-2"></i>Profile Info
                    </a>
                    <a href="{{ route('wallet.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-wallet2 me-2"></i>Wallet
                    </a>
                    <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-bag me-2"></i>Orders
                    </a>
                    <a href="{{ route('kyc.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield-check me-2"></i>KYC Verification
                    </a>
                    <a href="{{ route('disputes.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-exclamation-triangle me-2"></i>Support Tickets
                    </a>
                    <a href="{{ route('profile.security') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield-lock me-2"></i>Security
                    </a>
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div class="col-lg-9">

            {{-- Profile Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                       class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Username <span class="text-muted">(for storefront URL)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted small">volamani.com/store/</span>
                                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                                           class="form-control @error('username') is-invalid @enderror"
                                           placeholder="yourname">
                                </div>
                                @error('username')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Phone Number</label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       placeholder="+234 800 000 0000">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">WhatsApp Number</label>
                                <input type="tel" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}"
                                       class="form-control @error('whatsapp') is-invalid @enderror"
                                       placeholder="+234 800 000 0000">
                                @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Location</label>
                                <input type="text" name="location" value="{{ old('location', $user->location) }}"
                                       class="form-control" placeholder="Lagos, Nigeria">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Account Type</label>
                                <select name="user_type" class="form-select">
                                    @foreach(\App\Enums\UserType::cases() as $type)
                                        <option value="{{ $type->value }}" {{ $user->user_type === $type ? 'selected' : '' }}>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium small">Bio</label>
                                <textarea name="bio" rows="3"
                                          class="form-control @error('bio') is-invalid @enderror"
                                          placeholder="Tell buyers about yourself...">{{ old('bio', $user->bio) }}</textarea>
                                @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Change Password --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium small">Current Password</label>
                                <input type="password" name="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       placeholder="••••••••">
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium small">New Password</label>
                                <input type="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min 8 characters">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium small">Confirm New Password</label>
                                <input type="password" name="password_confirmation"
                                       class="form-control" placeholder="Repeat password">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-outline-danger px-4">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
