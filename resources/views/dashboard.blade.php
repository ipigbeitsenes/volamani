@extends('layouts.account')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- Welcome bar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Welcome back, {{ $user->name }}!</h4>
            <p class="text-muted mb-0 small">Here's what's happening with your account</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.onboarding') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-rocket me-1"></i>Start Selling
            </a>
        </div>
    </div>

    @if(! $user->hasVerifiedEmail())
        <div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
            <div>
                <strong>Please verify your email.</strong>
                Your account features are limited until your email is verified.
                <form method="POST" action="{{ route('verification.resend') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0 ms-2 text-warning fw-semibold">Resend verification email</button>
                </form>
            </div>
        </div>
    @endif

    {{-- KYC alert --}}
    @if(! $user->isKYCVerified())
        <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-shield-exclamation fs-4"></i>
            <div>
                <strong>Complete Identity Verification (KYC)</strong>
                to unlock withdrawals, escrow protection, and increased trust.
                <a href="{{ route('kyc.index') }}" class="btn btn-sm btn-outline-primary ms-2">Verify Now</a>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @php
            $walletBalance = $user->wallet?->balance ?? 0;
            $totalOrders   = $user->orders()->count();
        @endphp
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="text-muted small mb-1">Wallet Balance</div>
                <div class="fw-bold fs-4">{{ money($walletBalance) }}</div>
                <a href="{{ route('wallet.index') }}" class="small text-primary mt-1">View wallet</a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="text-muted small mb-1">Total Orders</div>
                <div class="fw-bold fs-4">{{ $totalOrders }}</div>
                <a href="{{ route('orders.index') }}" class="small text-primary mt-1">View orders</a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="text-muted small mb-1">Referrals</div>
                <div class="fw-bold fs-4">{{ $user->referrals()->count() }}</div>
                <span class="small text-muted mt-1">Code: <strong>{{ $user->referral_code }}</strong></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="text-muted small mb-1">KYC Status</div>
                <div class="mt-1">
                    <span class="badge bg-{{ $user->kyc_status?->badge() ?? 'secondary' }}">{{ $user->kyc_status?->label() ?? 'Unverified' }}</span>
                </div>
                <a href="{{ route('kyc.index') }}" class="small text-primary mt-1">Manage KYC</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Recent Orders --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h6 class="fw-bold mb-0">Recent Orders</h6>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @forelse($recentOrders as $order)
                        <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                            <div class="bg-light rounded p-2 flex-shrink-0">
                                <i class="bi bi-bag text-primary fs-5"></i>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-medium text-truncate">Order #{{ $order->reference ?? $order->id }}</div>
                                <div class="text-muted small">{{ $order->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">{{ money($order->total_amount ?? 0) }}</div>
                                <span class="badge bg-{{ $order->status->badge() }} small">{{ $order->status->label() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bag fs-1 d-block mb-2 opacity-25"></i>
                            <p class="mb-2">No orders yet</p>
                            <a href="{{ route('marketplace.products.index') }}" class="btn btn-primary btn-sm">Explore Marketplace</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">Quick Actions</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('marketplace.products.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary"></i> Browse Products
                    </a>
                    <a href="{{ route('marketplace.services.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-briefcase text-primary"></i> Hire a Freelancer
                    </a>
                    <a href="{{ route('marketplace.consultants.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-check text-primary"></i> Book a Consultant
                    </a>
                    <a href="{{ route('requests.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-megaphone text-primary"></i> Post a Request
                    </a>
                    <a href="{{ route('wallet.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-wallet2 text-primary"></i> Fund Wallet
                    </a>
                    <a href="{{ route('vendor.onboarding') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-shop text-primary"></i> Become a Vendor
                    </a>
                </div>
            </div>

            {{-- Referral card --}}
            <div class="card border-0 shadow-sm border-start border-primary border-3 mb-3">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-share text-primary me-2"></i>Refer & Earn</h6>
                    <p class="text-muted small mb-2">Share your referral link and earn commission on every referred sale.</p>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control bg-light"
                               value="{{ url('/register?ref=' . $user->referral_code) }}"
                               id="referralLink" readonly>
                        <button class="btn btn-primary" onclick="copyReferral()">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Buyer guidelines & policies --}}
            @include('partials.guidelines-card', ['audience' => 'buyer'])
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function copyReferral() {
    const input = document.getElementById('referralLink');
    navigator.clipboard.writeText(input.value);
    const btn = input.nextElementSibling;
    btn.innerHTML = '<i class="bi bi-check-lg"></i>';
    setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i>', 2000);
}
</script>
@endpush
