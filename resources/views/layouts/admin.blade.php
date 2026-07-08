<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.favicon')
    <title>@yield('title', 'Admin') — Volamani Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --vl-sidebar-bg: #1e1b4b;
            --vl-sidebar-hover: rgba(255,255,255,0.08);
            --vl-sidebar-active: #4f46e5;
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
            color: #a5b4fc;
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
            color: #6366f1;
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
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.5);
            z-index: 99;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .sidebar-backdrop.show { display: block; }
        }
    </style>

    @stack('styles')
</head>
<body>

    <nav class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <i class="bi bi-shield-shaded"></i>
            Volamani Admin
            <span class="badge bg-danger ms-auto">Admin</span>
        </a>

        <div class="py-2">
            <span class="nav-section">Overview</span>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ active_route('admin.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('admin.audit-logs') }}" class="nav-link {{ active_route('admin.audit-logs') }}">
                <i class="bi bi-journal-text"></i> Audit Logs
            </a>
            <a href="{{ route('admin.security.index') }}" class="nav-link {{ active_prefix('admin/security') }}">
                <i class="bi bi-shield-lock"></i> Security
            </a>

            <span class="nav-section">Users</span>
            @can('users.manage')
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ active_prefix('admin/users') }}">
                <i class="bi bi-people"></i> All Users
            </a>
            @endcan
            <a href="{{ route('admin.vendors.index') }}" class="nav-link {{ active_prefix('admin/vendors') }}">
                <i class="bi bi-shop"></i> Vendors
            </a>
            <a href="{{ route('admin.kyc.index') }}" class="nav-link {{ active_prefix('admin/kyc') }}">
                <i class="bi bi-shield-check"></i> KYC Verification
            </a>

            <span class="nav-section">Marketplace</span>
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ active_prefix('admin/products') }}">
                <i class="bi bi-box-seam"></i> Products
            </a>
            <a href="{{ route('admin.category-requests.index') }}" class="nav-link {{ active_prefix('admin/category-requests') }}">
                <i class="bi bi-tags"></i> Category Requests
            </a>
            <a href="{{ route('admin.disputes.index') }}" class="nav-link {{ active_prefix('admin/disputes') }}">
                <i class="bi bi-exclamation-triangle"></i> Disputes
            </a>
            <a href="{{ route('admin.returns.index') }}" class="nav-link {{ active_prefix('admin/returns') }}">
                <i class="bi bi-arrow-return-left"></i> Returns
            </a>
            @php($vlCbOpen = app(\App\Repositories\Chargebacks\ChargebackRepository::class)->openCount())
            <a href="{{ route('admin.chargebacks.index') }}" class="nav-link {{ active_prefix('admin/chargebacks') }}">
                <i class="bi bi-shield-exclamation"></i> Chargebacks
                @if($vlCbOpen > 0)
                    <span class="badge bg-danger rounded-pill ms-auto">{{ $vlCbOpen }}</span>
                @endif
            </a>
            @php($vlChatUnread = app(\App\Repositories\Chat\ChatRepository::class)->unansweredCount())
            <a href="{{ route('admin.live-chat.index') }}" class="nav-link {{ active_prefix('admin/live-chat') }}">
                <i class="bi bi-chat-dots"></i> Live Chat
                @if($vlChatUnread > 0)
                    <span class="badge bg-danger rounded-pill ms-auto">{{ $vlChatUnread }}</span>
                @endif
            </a>

            <span class="nav-section">Finance</span>
            <a href="{{ route('admin.payments.index') }}" class="nav-link {{ active_prefix('admin/payments') }}">
                <i class="bi bi-credit-card"></i> Payments
            </a>
            @can('withdrawals.approve')
            <a href="{{ route('admin.withdrawals.index') }}" class="nav-link {{ active_prefix('admin/withdrawals') }}">
                <i class="bi bi-arrow-up-circle"></i> Withdrawals
            </a>
            @endcan
            @can('commissions.manage')
            <a href="{{ route('admin.commissions.index') }}" class="nav-link {{ active_prefix('admin/commissions') }}">
                <i class="bi bi-percent"></i> Commissions
            </a>
            @endcan
            <a href="{{ route('admin.affiliates.index') }}" class="nav-link {{ active_prefix('admin/affiliates') }}">
                <i class="bi bi-share"></i> Affiliates
            </a>

            <span class="nav-section">Platform</span>
            <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ active_prefix('admin/subscriptions') }}">
                <i class="bi bi-star"></i> Subscriptions
            </a>
            <a href="{{ route('admin.documents.index') }}" class="nav-link {{ active_prefix('admin/documents') }}">
                <i class="bi bi-file-earmark-text"></i> Invoices &amp; Contracts
            </a>
            @can('settings.manage')
            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ active_prefix('admin/settings') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
            @endcan
        </div>
    </nav>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main-content">
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
            <div class="d-flex align-items-center gap-2">
                @include('layouts.partials.notification-bell')
                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=32&background=1e1b4b&color=fff"
                             class="rounded-circle" width="32" height="32" alt="">
                        <span class="small fw-medium">{{ auth()->user()->name }}</span>
                        @role('super-admin')
                            <span class="badge bg-danger">Super Admin</span>
                        @else
                            <span class="badge bg-secondary">Admin</span>
                        @endrole
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
        </div>

        <div class="page-content">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            document.getElementById('sidebarToggle')?.addEventListener('click', function () {
                sidebar.classList.toggle('open');
                backdrop.classList.toggle('show');
            });
            backdrop?.addEventListener('click', function () {
                sidebar.classList.remove('open');
                backdrop.classList.remove('show');
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
