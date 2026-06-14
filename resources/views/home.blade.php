@extends('layouts.app')

@section('title', 'Volamani')
@section('meta_description', "Volamani helps individuals, startups, companies and agencies grow their online presence — a branded storefront to sell digital products, services and physical goods, with escrow-protected payments that bring buyers and sellers together.")

@section('content')

{{-- ───────────────────────── Hero ───────────────────────── --}}
<section class="position-relative overflow-hidden" style="background: var(--vl-gradient-dark);">
    {{-- decorative glow blobs --}}
    <div class="position-absolute rounded-circle" style="width:480px;height:480px;top:-160px;right:-120px;background:radial-gradient(circle, rgba(245,158,11,.28), transparent 70%);"></div>
    <div class="position-absolute rounded-circle" style="width:520px;height:520px;bottom:-220px;left:-160px;background:radial-gradient(circle, rgba(79,70,229,.45), transparent 70%);"></div>

    <div class="container position-relative py-5" style="min-height:560px;">
        <div class="row align-items-center g-5 py-4">
            <div class="col-lg-6">
                <span class="badge rounded-pill mb-3 px-3 py-2 fw-semibold glass text-white">
                    <i class="bi bi-stars text-warning me-1"></i> Grow your online presence — Naira-first
                </span>
                <h1 class="display-3 fw-bold text-white lh-1 mb-4">
                    Grow online.<br>
                    Bring buyers &amp; sellers <span style="background:linear-gradient(120deg,#fbbf24,#f59e0b);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;">together.</span>
                </h1>
                <p class="fs-5 mb-4" style="color:rgba(255,255,255,.72);max-width:540px;">
                    Volamani gives individuals, startups, companies and agencies a branded storefront to sell
                    digital products, services and physical goods — with escrow-protected payments on every deal.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-warning btn-lg fw-bold px-4">
                        <i class="bi bi-shop me-2"></i>Create Your Store
                    </a>
                    <a href="{{ route('marketplace.products.index') }}" class="btn btn-outline-light btn-lg px-4">
                        Explore Marketplace
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-4 mt-5">
                    @foreach([
                        [number_format($stats['vendors']).'+', 'Verified Vendors'],
                        [number_format($stats['buyers']).'+', 'Happy Buyers'],
                        ['100%', 'Escrow Protected'],
                    ] as $s)
                    <div class="text-white">
                        <div class="fw-bold fs-3">{{ $s[0] }}</div>
                        <div style="color:rgba(255,255,255,.6)" class="small">{{ $s[1] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block">
                <div class="glass rounded-4 p-4 shadow-lg position-relative" style="box-shadow:var(--vl-shadow-lg);">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-white fw-semibold"><i class="bi bi-shop me-2"></i>Your Storefront</span>
                        <span class="badge badge-soft-success bg-white"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                    </div>
                    <div class="row g-3">
                        @foreach([
                            ['bi-box-seam','Digital Products','Sell eBooks, templates & code'],
                            ['bi-briefcase','Freelance Services','Hire & deliver with revisions'],
                            ['bi-calendar2-check','Consultations','Book expert sessions'],
                            ['bi-shield-check','Escrow Wallet','Funds released on delivery'],
                        ] as $f)
                        <div class="col-6">
                            <div class="p-3 rounded-3 h-100" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);">
                                <i class="bi {{ $f[0] }} text-warning fs-3 mb-2 d-block"></i>
                                <div class="text-white fw-semibold small">{{ $f[1] }}</div>
                                <div style="color:rgba(255,255,255,.55);font-size:.72rem">{{ $f[2] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-3 p-3 rounded-3" style="background:rgba(255,255,255,.95)">
                        <div>
                            <div class="text-muted" style="font-size:.7rem">Available balance</div>
                            <div class="fw-bold fs-5 text-dark">₦248,500</div>
                        </div>
                        <span class="btn btn-primary btn-sm">Withdraw <i class="bi bi-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- trust strip --}}
    <div class="border-top" style="border-color:rgba(255,255,255,.1) !important;background:rgba(0,0,0,.15)">
        <div class="container py-3">
            <div class="row text-center g-2 small" style="color:rgba(255,255,255,.7)">
                @foreach([
                    ['bi-credit-card-2-front','Paystack & Bank Transfer'],
                    ['bi-shield-lock','BVN / NIN Verification'],
                    ['bi-whatsapp','WhatsApp Commerce'],
                    ['bi-cash-stack','Same-day Withdrawals'],
                ] as $t)
                <div class="col-6 col-md-3"><i class="bi {{ $t[0] }} me-2 text-warning"></i>{{ $t[1] }}</div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ──────────────── Explore the platform (quick nav) ──────────────── --}}
<section class="bg-surface border-bottom py-3">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-center gap-2">
            @foreach([
                ['bi-box-seam','Digital Products', route('marketplace.products.index')],
                ['bi-briefcase','Freelance Services', route('marketplace.services.index')],
                ['bi-calendar2-check','Consultants', route('marketplace.consultants.index')],
                ['bi-shop','Browse Stores', route('vendors.index')],
                ['bi-megaphone','Post a Request', route('marketplace.requests.index')],
                ['bi-calculator','Pricing Assistant', route('pricing-calculator.index')],
                ['bi-cart3','Cart', route('cart.index')],
            ] as $nav)
                <a href="{{ $nav[2] }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="bi {{ $nav[0] }} me-1 text-primary"></i>{{ $nav[1] }}
                </a>
            @endforeach
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-speedometer2 me-1"></i>My Dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-shop me-1"></i>Start Selling
                </a>
            @endauth
        </div>
    </div>
</section>

{{-- ──────────────────── Categories ──────────────────── --}}
<section class="section bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow"><i class="bi bi-compass"></i> Discover</span>
            <h2 class="fw-bold mt-2">Explore the Marketplace</h2>
            <p class="lead-muted">Find exactly what you need across our growing ecosystem.</p>
        </div>
        <div class="row g-3 g-md-4">
            @foreach([
                ['bi-code-slash','Web Development','primary'],
                ['bi-palette','Graphic Design','danger'],
                ['bi-robot','AI Automation','success'],
                ['bi-megaphone','Digital Marketing','warning'],
                ['bi-camera-video','Video & Animation','info'],
                ['bi-book','eBooks & Courses','secondary'],
                ['bi-bar-chart','Business Strategy','primary'],
                ['bi-phone','Mobile Apps','danger'],
            ] as $cat)
            <div class="col-6 col-md-3">
                <a href="{{ route('marketplace.services.index', ['q' => $cat[1]]) }}" class="card hover-lift text-decoration-none h-100 text-center p-3">
                    <div class="card-body">
                        <div class="feature-tile mx-auto mb-3 bg-{{ $cat[2] }} bg-opacity-10 text-{{ $cat[2] }}">
                            <i class="bi {{ $cat[0] }}"></i>
                        </div>
                        <h6 class="fw-semibold text-dark mb-0">{{ $cat[1] }}</h6>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ──────────────────── Who it's for ──────────────────── --}}
<section class="section bg-white border-top">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow"><i class="bi bi-people"></i> Who it's for</span>
            <h2 class="fw-bold mt-2">Built to grow every kind of seller</h2>
            <p class="lead-muted">From a one-person side hustle to a full agency — establish your online presence and reach more buyers.</p>
        </div>
        <div class="row g-4">
            @foreach([
                ['bi-person','Individuals','primary','Freelancers, creators and independent sellers — launch a storefront in minutes and start earning.'],
                ['bi-rocket-takeoff','Startups','success','Validate, sell and collect payments fast, with escrow building buyer trust from day one.'],
                ['bi-building','Companies','info','Give your business a professional online presence and a second sales channel that runs itself.'],
                ['bi-diagram-3','Agencies','warning','Showcase services, manage clients and take bookings — all under your own branded store.'],
            ] as $a)
            <div class="col-6 col-lg-3">
                <div class="card hover-lift h-100 text-center p-3">
                    <div class="card-body">
                        <div class="feature-tile mx-auto mb-3 bg-{{ $a[2] }} bg-opacity-10 text-{{ $a[2] }}">
                            <i class="bi {{ $a[0] }}"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $a[1] }}</h5>
                        <p class="text-muted small mb-0">{{ $a[3] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('register') }}" class="btn btn-primary px-4">Start growing free <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>

{{-- ──────────────────── Pillars ──────────────────── --}}
<section class="section bg-surface">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow"><i class="bi bi-grid-3x3-gap"></i> One platform</span>
            <h2 class="fw-bold mt-2">Everything your business needs</h2>
            <p class="lead-muted">Shopify + Fiverr + Calendly + Selar — combined and localized.</p>
        </div>
        <div class="row g-4">
            @foreach([
                ['bi-bag-check','Digital Products','Buy eBooks, templates, UI kits, music and software with instant delivery.','26,86,219', route('marketplace.products.index')],
                ['bi-briefcase','Freelance Services','Hire vetted talent with packages, requirements, revisions and delivery tracking.','79,70,229', route('marketplace.services.index')],
                ['bi-people','Reverse Requests','Post what you need and qualified sellers send you proposals.','5,150,105', route('marketplace.requests.index')],
                ['bi-calendar2-week','Consultations','Book paid one-on-one sessions with calendar and meeting links.','217,119,6', route('marketplace.consultants.index')],
                ['bi-calculator','Pricing Assistant','Estimate fair project pricing in seconds before you buy or sell.','220,38,38', route('pricing-calculator.index')],
                ['bi-patch-check','Trust, Escrow & KYC','Verified badges, real reviews, escrow on every deal and admin-mediated support.','2,132,199', route('register')],
            ] as $p)
            <div class="col-md-6 col-lg-4">
                <a href="{{ $p[4] }}" class="card hover-lift h-100 p-2 text-decoration-none text-dark">
                    <div class="card-body">
                        <div class="feature-tile mb-3" style="background:rgba({{ $p[3] }},.1);color:rgb({{ $p[3] }});">
                            <i class="bi {{ $p[0] }}"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $p[1] }} <i class="bi bi-arrow-right small ms-1"></i></h5>
                        <p class="text-muted small mb-0">{{ $p[2] }}</p>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ──────────────────── Why + Steps ──────────────────── --}}
<section class="section bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-geo-alt"></i> African-first</span>
                <h2 class="fw-bold mt-2 mb-4">Built for Nigerian &amp; African<br>business realities</h2>
                <div class="d-flex flex-column gap-3">
                    @foreach([
                        ['bi-bank','Bank Transfer Payments','Pay via Paystack or manual bank transfer — no card required.'],
                        ['bi-shield-lock','Escrow Protection','Funds held safely until delivery is confirmed.'],
                        ['bi-chat-dots','WhatsApp Commerce','Vendors display WhatsApp for direct negotiations.'],
                        ['bi-award','Verified Vendors','KYC, BVN/NIN and CAC verification build real trust.'],
                        ['bi-currency-exchange','Flexible Pricing','Fixed, milestone and installment payment options.'],
                    ] as $why)
                    <div class="d-flex gap-3">
                        <div class="feature-tile sm bg-primary bg-opacity-10 text-primary flex-shrink-0">
                            <i class="bi {{ $why[0] }}"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $why[1] }}</div>
                            <div class="text-muted small">{{ $why[2] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow border-0 p-4 p-md-5 position-relative overflow-hidden">
                    <div class="position-absolute rounded-circle" style="width:200px;height:200px;top:-60px;right:-60px;background:var(--vl-gradient-soft);"></div>
                    <span class="eyebrow position-relative"><i class="bi bi-rocket-takeoff"></i> Get going</span>
                    <h4 class="fw-bold mt-2 mb-4 position-relative">Start earning in minutes</h4>
                    <div class="d-flex flex-column gap-3 position-relative">
                        @foreach([
                            'Create your free account',
                            'Set up your branded storefront',
                            'List products or services',
                            'Get paid securely via escrow',
                        ] as $i => $step)
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0 text-white"
                                 style="width:38px;height:38px;background:var(--vl-gradient)">{{ $i+1 }}</div>
                            <span class="fw-medium">{{ $step }}</span>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('register') }}" class="btn btn-primary mt-4 py-2 fw-semibold position-relative">
                        Get Started Free <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ──────────────────── Subscription Plans ──────────────────── --}}
@if($plans->isNotEmpty())
<section class="section bg-surface border-top">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow"><i class="bi bi-stars"></i> Seller plans</span>
            <h2 class="fw-bold mt-2">Plans that grow with you</h2>
            <p class="lead-muted">Lower commission, higher limits and featured placement as you scale. Upgrade anytime.</p>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach($plans as $plan)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 {{ $plan->is_popular ? 'border-primary shadow' : 'border-0 shadow-sm' }} position-relative">
                    @if($plan->is_popular)
                        <span class="badge bg-primary position-absolute top-0 start-50 translate-middle px-3 py-2 rounded-pill">Most popular</span>
                    @endif
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold mb-1">{{ $plan->name }}</h5>
                        @if($plan->tagline)<p class="text-muted small mb-3">{{ $plan->tagline }}</p>@endif

                        <div class="mb-3">
                            <span class="display-6 fw-bold">{{ $plan->isFree() ? 'Free' : money($plan->price) }}</span>
                            @unless($plan->isFree())<span class="text-muted">{{ $plan->billing_interval->shortLabel() }}</span>@endunless
                        </div>

                        <ul class="list-unstyled small mb-4 flex-grow-1">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>{{ $plan->productLimitLabel() }} products</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>{{ $plan->serviceLimitLabel() }} services</li>
                            @if($plan->commission_rate !== null)
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>{{ rtrim(rtrim(number_format($plan->commission_rate, 2), '0'), '.') }}% commission</li>
                            @endif
                            @if($plan->featured_listing)
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Featured store placement</li>
                            @endif
                            @if($plan->hasTrial())
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>{{ $plan->trial_days }}-day free trial</li>
                            @endif
                            @foreach(($plan->perks ?? []) as $perk)
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>{{ $perk }}</li>
                            @endforeach
                        </ul>

                        @auth
                            <a href="{{ route('vendor.subscription.index') }}" class="btn {{ $plan->is_popular ? 'btn-primary' : 'btn-outline-primary' }} w-100">Choose {{ $plan->name }}</a>
                        @else
                            <a href="{{ route('register') }}" class="btn {{ $plan->is_popular ? 'btn-primary' : 'btn-outline-primary' }} w-100">Get started</a>
                        @endauth
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ──────────────────── CTA ──────────────────── --}}
<section class="section">
    <div class="container">
        <div class="rounded-4 text-center text-white p-5 position-relative overflow-hidden" style="background:var(--vl-gradient);box-shadow:var(--vl-shadow-lg);">
            <div class="position-absolute rounded-circle" style="width:360px;height:360px;top:-140px;left:-80px;background:rgba(255,255,255,.08);"></div>
            <div class="position-absolute rounded-circle" style="width:300px;height:300px;bottom:-150px;right:-60px;background:rgba(255,255,255,.08);"></div>
            <div class="position-relative py-3">
                <h2 class="fw-bold mb-3 text-white">Ready to grow your business?</h2>
                <p class="mb-4 fs-5" style="color:rgba(255,255,255,.85)">Join thousands of African entrepreneurs already building on Volamani.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('register') }}" class="btn btn-warning btn-lg fw-bold px-5">Create Free Account</a>
                    <a href="{{ route('marketplace.products.index') }}" class="btn btn-outline-light btn-lg px-4">Browse Marketplace</a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
