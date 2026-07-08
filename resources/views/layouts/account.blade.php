<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.favicon')
    <title>@yield('title', 'My Account') — Volamani</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --vl-primary: #1a56db;
            --vl-sidebar-bg: #0f172a;
            --vl-sidebar-text: #94a3b8;
            --vl-sidebar-active: #1a56db;
            --vl-sidebar-width: 260px;
        }
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .sidebar {
            width: var(--vl-sidebar-width);
            background: var(--vl-sidebar-bg);
            position: fixed; top: 0; left: 0; z-index: 100;
        }
        .sidebar-brand {
            font-weight: 800; font-size: 1.4rem; color: #fff;
            padding: 1.5rem 1.5rem 1rem; display: flex; align-items: center; gap: .6rem;
            text-decoration: none; border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar .nav-link {
            color: var(--vl-sidebar-text); padding: .65rem 1.5rem; display: flex;
            align-items: center; gap: .6rem; font-size: .9rem; transition: all .2s;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff; background-color: rgba(26,86,219,.15); border-left-color: var(--vl-sidebar-active);
        }
        .sidebar .nav-section {
            font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em;
            color: #475569; padding: 1.2rem 1.5rem .4rem;
        }
        .main-content { margin-left: var(--vl-sidebar-width); min-height: 100vh; }
        .topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0; padding: .75rem 1.5rem;
            position: sticky; top: 0; z-index: 99;
        }
        .page-content { padding: 1.5rem; }
        .card { border: 1px solid #e2e8f0; border-radius: 12px; }
    </style>
    @include('layouts.partials.dashboard-shell')
    @stack('styles')
</head>
<body>

@php
    $u = auth()->user();
    $isActiveVendor = $u->isVendor() && $u->vendor?->isActive();
@endphp

<nav class="sidebar" id="sidebar">
    <a href="{{ route('home') }}" class="sidebar-brand">
        <span class="d-inline-flex align-items-center justify-content-center text-white rounded-3"
              style="width:30px;height:30px;background:linear-gradient(135deg,#1a56db,#4f46e5);">
            <i class="bi bi-send-fill" style="font-size:.85rem;transform:rotate(45deg)"></i>
        </span>
        Volamani
    </a>

    <div class="py-2">
        <span class="nav-section">Account</span>
        <a href="{{ route('dashboard') }}" class="nav-link {{ active_route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>

        <span class="nav-section">Purchases</span>
        <a href="{{ route('orders.index') }}" class="nav-link {{ active_prefix('orders') }}"><i class="bi bi-bag-check"></i> Orders &amp; Downloads</a>
        <a href="{{ route('service-orders.index') }}" class="nav-link {{ active_prefix('service-orders') }}"><i class="bi bi-briefcase"></i> Service Orders</a>
        <a href="{{ route('consultations.sessions') }}" class="nav-link {{ active_prefix('consultations') }}"><i class="bi bi-calendar2-check"></i> Consultations</a>
        <a href="{{ route('requests.my') }}" class="nav-link {{ active_prefix('requests') }}"><i class="bi bi-megaphone"></i> My Requests</a>

        <span class="nav-section">Money &amp; Protection</span>
        <a href="{{ route('wallet.index') }}" class="nav-link {{ active_prefix('wallet') }}"><i class="bi bi-wallet2"></i> Wallet</a>
        <a href="{{ route('escrows.index') }}" class="nav-link {{ active_prefix('escrows') }}"><i class="bi bi-shield-lock"></i> Escrow</a>
        <a href="{{ route('returns.index') }}" class="nav-link {{ active_prefix('returns') }}"><i class="bi bi-arrow-return-left"></i> Returns</a>
        <a href="{{ route('disputes.index') }}" class="nav-link {{ active_prefix('disputes') }}"><i class="bi bi-life-preserver"></i> Support Tickets</a>
        <a href="{{ route('invoices.index') }}" class="nav-link {{ active_prefix('invoices') }}"><i class="bi bi-receipt"></i> Invoices</a>

        <span class="nav-section">Discover</span>
        <a href="{{ route('marketplace.products.index') }}" class="nav-link"><i class="bi bi-grid"></i> Marketplace</a>
        <a href="{{ route('vendors.index') }}" class="nav-link {{ active_prefix('vendors') }}"><i class="bi bi-shop"></i> Stores</a>
        <a href="{{ route('follow.index') }}" class="nav-link {{ active_prefix('following') }}"><i class="bi bi-person-heart"></i> Following</a>
        <a href="{{ route('matching.index') }}" class="nav-link {{ active_prefix('matching') }}"><i class="bi bi-diagram-3"></i> Match Me</a>

        <span class="nav-section">Settings</span>
        <a href="{{ route('kyc.index') }}" class="nav-link {{ active_prefix('kyc') }}"><i class="bi bi-patch-check"></i> Verification (KYC)</a>
        <a href="{{ route('profile.index') }}" class="nav-link {{ active_prefix('profile') }}"><i class="bi bi-person-circle"></i> Profile</a>
        <a href="{{ route('notifications.preferences') }}" class="nav-link {{ active_prefix('notifications') }}"><i class="bi bi-sliders"></i> Notifications</a>
        @if($isActiveVendor)
            <a href="{{ route('vendor.dashboard') }}" class="nav-link"><i class="bi bi-shop-window"></i> Vendor Dashboard</a>
        @else
            <a href="{{ route('vendor.onboarding') }}" class="nav-link"><i class="bi bi-rocket"></i> Become a Vendor</a>
        @endif
    </div>
</nav>

<div class="main-content">
    <div class="topbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">@yield('breadcrumb')</ol></nav>
        </div>
        <div class="d-flex align-items-center gap-3">
            @php $unread = $u->unreadNotifications()->count(); @endphp
            <a href="{{ route('notifications.index') }}" class="position-relative text-dark" aria-label="Notifications">
                <i class="bi bi-bell fs-5"></i>
                @if($unread > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.55rem;">{{ $unread > 9 ? '9+' : $unread }}</span>
                @endif
            </a>
            <a href="{{ route('marketplace.products.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-shop me-1"></i>Shop</a>
            <div class="dropdown">
                <button class="btn btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($u->name) }}&size=32&background=1a56db&color=fff" class="rounded-circle" width="30" height="30" alt="">
                    <span class="d-none d-md-inline small fw-medium">{{ $u->name }}</span>
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('wallet.index') }}"><i class="bi bi-wallet2 me-2"></i>Wallet</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    <div class="page-content pb-0">
        @foreach(['success' => 'check-circle-fill', 'error' => 'exclamation-triangle-fill', 'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'] as $key => $icon)
            @if(session($key))
                <div class="alert alert-{{ $key === 'error' ? 'danger' : $key }} alert-dismissible fade show">
                    <i class="bi bi-{{ $icon }} me-2"></i>{{ session($key) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following:</div>
                <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <div class="page-content">
        @yield('content')
    </div>
</div>

@include('layouts.partials.chat-widget')
@include('layouts.partials.dashboard-sidebar')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
