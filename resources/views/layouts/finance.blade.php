<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.favicon')
    <title>@yield('title', 'Finance') — Volamani</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --vl-sidebar-bg: #14532d;
            --vl-sidebar-hover: rgba(255,255,255,0.08);
            --vl-sidebar-active: #22c55e;
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
            font-size: 1.2rem;
            color: #ffffff;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-brand .badge { font-size: 0.6rem; }
        .sidebar .nav-link {
            color: #bbf7d0;
            padding: 0.6rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--vl-sidebar-hover);
            border-left-color: var(--vl-sidebar-active);
        }
        .sidebar .nav-section {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #4ade80;
            padding: 1.2rem 1.5rem 0.3rem;
        }
        .main-content { margin-left: var(--vl-sidebar-width); min-height: 100vh; }
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            position: sticky; top: 0; z-index: 99;
        }
        .page-content { padding: 1.5rem; }
        .card { border: 1px solid #e2e8f0; border-radius: 12px; }
    </style>

    @include('layouts.partials.dashboard-shell')
    @stack('styles')
</head>
<body>

    <nav class="sidebar" id="sidebar">
        <a href="{{ route('finance.dashboard') }}" class="sidebar-brand">
            <i class="bi bi-cash-coin"></i>
            Volamani
            <span class="badge ms-auto" style="background:#22c55e;">Finance</span>
        </a>

        <div class="py-2">
            <span class="nav-section">Overview</span>
            <a href="{{ route('finance.dashboard') }}" class="nav-link {{ active_route('finance.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <span class="nav-section">Money</span>
            <a href="{{ route('finance.payments.index') }}" class="nav-link {{ active_prefix('finance/payments') }}">
                <i class="bi bi-credit-card"></i> Payments
            </a>
            <a href="{{ route('finance.withdrawals.index') }}" class="nav-link {{ active_prefix('finance/withdrawals') }}">
                <i class="bi bi-arrow-up-circle"></i> Withdrawals
            </a>
            <a href="{{ route('finance.escrows.index') }}" class="nav-link {{ active_prefix('finance/escrows') }}">
                <i class="bi bi-safe2"></i> Escrow
            </a>

            <span class="nav-section">Settings</span>
            <a href="{{ route('finance.commissions.index') }}" class="nav-link {{ active_prefix('finance/commissions') }}">
                <i class="bi bi-percent"></i> Commissions &amp; Fees
            </a>
        </div>
    </nav>

    <div class="main-content">
        <div class="topbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @include('layouts.partials.notification-bell')
                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=32&background=14532d&color=fff"
                             class="rounded-circle" width="32" height="32" alt="">
                        <span class="small fw-medium">{{ auth()->user()->name }}</span>
                        <span class="badge bg-success">Finance</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
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
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <div class="page-content">
            @yield('content')
        </div>
    </div>

    @include('layouts.partials.dashboard-sidebar')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
