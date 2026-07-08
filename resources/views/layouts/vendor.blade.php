<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.favicon')
    <title>@yield('title', 'Vendor Dashboard') — Volamani</title>

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
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            overflow-y: auto;
        }
        .sidebar-brand {
            font-weight: 800;
            font-size: 1.4rem;
            color: #ffffff;
            padding: 1.5rem 1.5rem 1rem;
            display: block;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar .nav-link {
            color: var(--vl-sidebar-text);
            padding: 0.65rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border-radius: 0;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: rgba(26,86,219,0.15);
            border-left: 3px solid var(--vl-sidebar-active);
        }
        .sidebar .nav-section {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
            padding: 1.2rem 1.5rem 0.4rem;
        }
        .main-content {
            margin-left: var(--vl-sidebar-width);
            min-height: 100vh;
        }
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .page-content { padding: 1.5rem; }
        .card { border: 1px solid #e2e8f0; border-radius: 12px; }
        .stat-card { border-left: 4px solid var(--vl-primary); }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <a href="{{ route('vendor.dashboard') }}" class="sidebar-brand">
            <i class="bi bi-grid-fill text-primary me-2"></i>Volamani
        </a>

        <div class="py-2">
            <span class="nav-section">Main</span>
            <a href="{{ route('vendor.dashboard') }}" class="nav-link {{ active_route('vendor.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('vendor.analytics') }}" class="nav-link {{ active_route('vendor.analytics') }}">
                <i class="bi bi-bar-chart-line"></i> Analytics
            </a>

            <span class="nav-section">Products & Services</span>
            <a href="{{ route('vendor.products.index') }}" class="nav-link {{ active_prefix('vendor/products') }}">
                <i class="bi bi-box-seam"></i> Products
            </a>
            <a href="{{ route('vendor.services.index') }}" class="nav-link {{ active_prefix('vendor/services') }}">
                <i class="bi bi-briefcase"></i> Services
            </a>

            <span class="nav-section">Business</span>
            <a href="{{ route('vendor.orders.index') }}" class="nav-link {{ active_prefix('vendor/orders') }}">
                <i class="bi bi-bag-check"></i> Orders
            </a>
            <a href="{{ route('vendor.returns.index') }}" class="nav-link {{ active_prefix('vendor/returns') }}">
                <i class="bi bi-arrow-return-left"></i> Returns
            </a>
            <a href="{{ route('vendor.chargebacks.index') }}" class="nav-link {{ active_prefix('vendor/chargebacks') }}">
                <i class="bi bi-shield-exclamation"></i> Chargebacks
            </a>
            <a href="{{ route('vendor.quotations.index') }}" class="nav-link {{ active_prefix('vendor/quotations') }}">
                <i class="bi bi-file-earmark-text"></i> Quotations
            </a>
            <a href="{{ route('vendor.clients.index') }}" class="nav-link {{ active_prefix('vendor/clients') }}">
                <i class="bi bi-people"></i> Clients
            </a>
            <a href="{{ route('vendor.invoices.index') }}" class="nav-link {{ active_prefix('vendor/invoices') }}">
                <i class="bi bi-receipt"></i> Invoices
            </a>
            <a href="{{ route('vendor.contracts.index') }}" class="nav-link {{ active_prefix('vendor/contracts') }}">
                <i class="bi bi-file-earmark-check"></i> Contracts of Sale
            </a>
            <a href="{{ route('vendor.estimates.index') }}" class="nav-link {{ active_prefix('vendor/estimates') }}">
                <i class="bi bi-calculator"></i> Pricing Estimates
            </a>
            <a href="{{ route('vendor.matching.index') }}" class="nav-link {{ active_prefix('vendor/matching') }}">
                <i class="bi bi-diagram-3"></i> Leads &amp; Matching
            </a>

            <span class="nav-section">Consultations</span>
            <a href="{{ route('vendor.consultations.index') }}" class="nav-link {{ active_prefix('vendor/consultations') }}">
                <i class="bi bi-calendar2-check"></i> Sessions
            </a>
            <a href="{{ route('vendor.consultations.packages') }}" class="nav-link {{ active_route('vendor.consultations.packages') }}">
                <i class="bi bi-collection"></i> Packages
            </a>

            <span class="nav-section">Finance</span>
            <a href="{{ route('vendor.wallet.index') }}" class="nav-link {{ active_prefix('vendor/wallet') }}">
                <i class="bi bi-wallet2"></i> Wallet
            </a>
            <a href="{{ route('vendor.affiliates.index') }}" class="nav-link {{ active_prefix('vendor/affiliates') }}">
                <i class="bi bi-share"></i> Affiliates
            </a>
            <a href="{{ route('vendor.subscription.index') }}" class="nav-link {{ active_prefix('vendor/subscription') }}">
                <i class="bi bi-star"></i> Subscription
            </a>

            <span class="nav-section">Account</span>
            <a href="{{ route('vendor.storefront') }}" class="nav-link {{ active_route('vendor.storefront') }}">
                <i class="bi bi-shop"></i> Storefront
            </a>
            <a href="{{ route('vendor.category-requests.index') }}" class="nav-link {{ active_prefix('vendor/category-requests') }}">
                <i class="bi bi-tags"></i> Category Requests
            </a>
            <a href="{{ route('vendor.kyc.index') }}" class="nav-link {{ active_prefix('vendor/kyc') }}">
                <i class="bi bi-shield-check"></i> Verification
            </a>
            <a href="{{ route('profile.index') }}" class="nav-link {{ active_prefix('profile') }}">
                <i class="bi bi-person-circle"></i> Profile
            </a>
        </div>
    </nav>

    <!-- Main content -->
    <div class="main-content">
        <!-- Top bar -->
        <div class="topbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="bi bi-globe me-1"></i>View Store
                </a>
                @include('layouts.partials.notification-bell')
                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=32&background=1a56db&color=fff"
                             class="rounded-circle" width="32" height="32" alt="">
                        <span class="d-none d-md-inline small fw-medium">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
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

        <!-- Flash messages -->
        <div class="page-content pb-0">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following before submitting:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <div class="page-content">
            @yield('content')
        </div>
    </div>

    @include('layouts.partials.chat-widget')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });
    </script>
    @stack('scripts')
</body>
</html>
