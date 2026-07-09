@once
<style>
    /* Mobile: let the collapsed menu scroll on its own instead of dragging the
       page body with it (overscroll-behavior stops the scroll from chaining). */
    @media (max-width: 991.98px) {
        #navMain {
            max-height: calc(100dvh - 66px);
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        #navMain .dropdown-menu {
            max-height: 55vh;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
    }
</style>
@endonce
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top shadow-sm">
    <div class="container">
        @php $vlLogo = settings('site_logo'); $vlSiteName = settings('site_name', 'Volamani'); @endphp
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}" style="font-size: 1.4rem;">
            @if($vlLogo)
                <img src="{{ media_url($vlLogo) }}" alt="{{ $vlSiteName }}" style="height:38px;width:auto;max-width:180px;object-fit:contain;">
            @else
                <span class="d-inline-flex align-items-center justify-content-center text-white rounded-3"
                      style="width:34px;height:34px;background:var(--vl-gradient);box-shadow:0 6px 14px -6px rgba(26,86,219,.6);">
                    <i class="bi bi-send-fill" style="font-size:.95rem;transform:rotate(45deg)"></i>
                </span>
                <span>{{ $vlSiteName }}</span>
            @endif
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ active_route('marketplace.products.index') }}" href="{{ route('marketplace.products.index') }}">
                        Products
                    </a>
                </li>
                @feature('services')
                <li class="nav-item">
                    <a class="nav-link {{ active_route('marketplace.services.index') }}" href="{{ route('marketplace.services.index') }}">
                        Services
                    </a>
                </li>
                @endfeature
                @feature('consultations')
                <li class="nav-item">
                    <a class="nav-link {{ active_route('marketplace.consultants.index') }}" href="{{ route('marketplace.consultants.index') }}">
                        Consultants
                    </a>
                </li>
                @endfeature
                <li class="nav-item">
                    <a class="nav-link {{ active_route('vendors.index') }}" href="{{ route('vendors.index') }}">
                        Stores
                    </a>
                </li>
                @feature('requests')
                <li class="nav-item">
                    <a class="nav-link {{ active_route('marketplace.requests.index') }}" href="{{ route('marketplace.requests.index') }}">
                        Post a Request
                    </a>
                </li>
                @endfeature
                @feature('pricing_calculator')
                <li class="nav-item">
                    <a class="nav-link {{ active_prefix('marketplace/pricing-calculator') }}" href="{{ route('pricing-calculator.index') }}">
                        Pricing
                    </a>
                </li>
                @endfeature
                @auth
                    @feature('matching')
                    <li class="nav-item">
                        <a class="nav-link {{ active_prefix('marketplace/matching') }}" href="{{ route('matching.index') }}">
                            Match Me
                        </a>
                    </li>
                    @endfeature
                @endauth
            </ul>

            <ul class="navbar-nav align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link position-relative" href="{{ route('cart.index') }}" aria-label="Cart">
                        <i class="bi bi-cart3 fs-5"></i>
                        @php $vlmCart = app(\App\Services\Cart\CartService::class)->count(); @endphp
                        @if($vlmCart > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:.6rem;">
                                {{ $vlmCart > 9 ? '9+' : $vlmCart }}
                                <span class="visually-hidden">items in cart</span>
                            </span>
                        @endif
                    </a>
                </li>
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Sign In</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3" href="{{ route('register') }}">Get Started</a>
                    </li>
                @else
                    @php
                        $vlmUnread = auth()->user()->unreadNotifications()->count();
                        $vlmRecent = auth()->user()->notifications()->limit(6)->get();
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-speedometer2"></i><span class="d-lg-inline">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                            @if($vlmUnread > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">
                                    {{ $vlmUnread > 9 ? '9+' : $vlmUnread }}
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 340px;">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <span class="fw-semibold small">Notifications</span>
                                @if($vlmUnread > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}" class="m-0">
                                        @csrf
                                        <button class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <div style="max-height: 360px; overflow-y: auto;">
                                @forelse($vlmRecent as $note)
                                    @php
                                        $d   = $note->data;
                                        $cat = isset($d['category']) ? \App\Enums\NotificationCategory::tryFrom($d['category']) : null;
                                    @endphp
                                    <a href="{{ route('notifications.open', $note->id) }}"
                                       class="dropdown-item d-flex gap-2 align-items-start py-2 {{ $note->read_at ? '' : 'bg-light' }}"
                                       style="white-space: normal;">
                                        <i class="bi {{ $d['icon'] ?? $cat?->icon() ?? 'bi-bell' }} text-{{ $cat?->color() ?? 'primary' }} mt-1"></i>
                                        <span class="flex-grow-1">
                                            <span class="d-block fw-semibold small">{{ $d['title'] ?? 'Notification' }}</span>
                                            <span class="d-block text-muted" style="font-size:.78rem;">{{ Str::limit($d['message'] ?? '', 70) }}</span>
                                            <span class="d-block text-muted" style="font-size:.7rem;">{{ $note->created_at->diffForHumans() }}</span>
                                        </span>
                                    </a>
                                @empty
                                    <div class="text-center text-muted small py-4">
                                        <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>No notifications yet
                                    </div>
                                @endforelse
                            </div>
                            <a href="{{ route('notifications.index') }}" class="dropdown-item text-center border-top small py-2">
                                View all notifications
                            </a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <button class="btn btn-sm d-flex align-items-center gap-2 nav-link" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=32&background=1a56db&color=fff"
                                 class="rounded-circle" width="28" height="28" alt="">
                            <span class="small">{{ auth()->user()->name }}</span>
                            <i class="bi bi-chevron-down small"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 260px;">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</a></li>

                            <li><h6 class="dropdown-header text-uppercase small text-muted">Purchases</h6></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-bag-check me-2"></i>My Orders &amp; Downloads</a></li>
                            @feature('services')<li><a class="dropdown-item" href="{{ route('service-orders.index') }}"><i class="bi bi-briefcase me-2"></i>Service Orders</a></li>@endfeature
                            @feature('consultations')<li><a class="dropdown-item" href="{{ route('consultations.sessions') }}"><i class="bi bi-calendar2-check me-2"></i>My Consultations</a></li>@endfeature
                            @feature('requests')<li><a class="dropdown-item" href="{{ route('requests.my') }}"><i class="bi bi-megaphone me-2"></i>My Requests</a></li>@endfeature

                            <li><h6 class="dropdown-header text-uppercase small text-muted">Money &amp; Protection</h6></li>
                            @feature('wallet')<li><a class="dropdown-item" href="{{ route('wallet.index') }}"><i class="bi bi-wallet2 me-2"></i>Wallet</a></li>@endfeature
                            @feature('escrow')<li><a class="dropdown-item" href="{{ route('escrows.index') }}"><i class="bi bi-shield-lock me-2"></i>Escrow</a></li>@endfeature
                            <li><a class="dropdown-item" href="{{ route('disputes.index') }}"><i class="bi bi-life-preserver me-2"></i>Support Tickets</a></li>
                            @feature('invoices')<li><a class="dropdown-item" href="{{ route('invoices.index') }}"><i class="bi bi-receipt me-2"></i>Invoices</a></li>@endfeature

                            <li><h6 class="dropdown-header text-uppercase small text-muted">Account</h6></li>
                            <li><a class="dropdown-item" href="{{ route('follow.index') }}"><i class="bi bi-person-heart me-2"></i>Following</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('notifications.preferences') }}"><i class="bi bi-sliders me-2"></i>Notification Settings</a></li>
                            <li><a class="dropdown-item" href="{{ route('vendor.dashboard') }}"><i class="bi bi-shop me-2"></i>Vendor Dashboard</a></li>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
