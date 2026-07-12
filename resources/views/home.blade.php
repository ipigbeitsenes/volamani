@extends('layouts.app')

{{-- No @section('title') on purpose: the home page uses the branded default
     "Volamani — Your Digital Business Ecosystem" from layouts.partials.seo. --}}
@section('meta_description', "Volamani helps individuals, startups, companies and agencies grow their online presence — a branded storefront to sell digital products, services and physical goods, with escrow-protected payments that bring buyers and sellers together.")

@push('styles')
<style>
    /* ════════════════ Home page — motion & polish ════════════════ */

    /* Scroll-reveal system */
    .reveal {
        opacity: 0;
        transform: translateY(34px);
        transition: opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1);
        will-change: opacity, transform;
    }
    .reveal.reveal-left  { transform: translateX(-40px); }
    .reveal.reveal-right { transform: translateX(40px); }
    .reveal.reveal-zoom  { transform: scale(.92); }
    .reveal.is-visible {
        opacity: 1;
        transform: none;
    }
    /* Stagger children */
    .reveal-stagger > * { opacity: 0; transform: translateY(28px); }
    .reveal-stagger.is-visible > * {
        opacity: 1; transform: none;
        transition: opacity .6s cubic-bezier(.16,1,.3,1), transform .6s cubic-bezier(.16,1,.3,1);
    }
    .reveal-stagger.is-visible > *:nth-child(1) { transition-delay: .05s; }
    .reveal-stagger.is-visible > *:nth-child(2) { transition-delay: .12s; }
    .reveal-stagger.is-visible > *:nth-child(3) { transition-delay: .19s; }
    .reveal-stagger.is-visible > *:nth-child(4) { transition-delay: .26s; }
    .reveal-stagger.is-visible > *:nth-child(5) { transition-delay: .33s; }
    .reveal-stagger.is-visible > *:nth-child(6) { transition-delay: .40s; }
    .reveal-stagger.is-visible > *:nth-child(7) { transition-delay: .47s; }
    .reveal-stagger.is-visible > *:nth-child(8) { transition-delay: .54s; }

    /* ── Hero ────────────────────────────────────────────────── */
    .vl-hero {
        background: var(--vl-gradient-dark);
        background-size: 180% 180%;
        animation: vlHeroShift 18s ease-in-out infinite;
    }
    @keyframes vlHeroShift {
        0%   { background-position: 0% 50%; }
        50%  { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    /* Floating aurora blobs */
    .vl-blob { position: absolute; border-radius: 50%; filter: blur(2px); pointer-events: none; }
    .vl-blob-1 { width: 480px; height: 480px; top: -160px; right: -120px;
        background: radial-gradient(circle, rgba(245,158,11,.30), transparent 70%);
        animation: vlFloat1 16s ease-in-out infinite; }
    .vl-blob-2 { width: 540px; height: 540px; bottom: -240px; left: -180px;
        background: radial-gradient(circle, rgba(79,70,229,.50), transparent 70%);
        animation: vlFloat2 20s ease-in-out infinite; }
    .vl-blob-3 { width: 320px; height: 320px; top: 30%; left: 40%;
        background: radial-gradient(circle, rgba(59,130,246,.28), transparent 70%);
        animation: vlFloat1 24s ease-in-out infinite reverse; }
    @keyframes vlFloat1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(30px,-40px) scale(1.08); } }
    @keyframes vlFloat2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-40px,30px) scale(1.12); } }

    /* Subtle grid overlay on hero */
    .vl-hero-grid {
        position: absolute; inset: 0; pointer-events: none; opacity: .35;
        background-image:
            linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
        background-size: 54px 54px;
        mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, #000 30%, transparent 75%);
        -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, #000 30%, transparent 75%);
    }

    /* Shimmering gradient text */
    .vl-shimmer {
        background: linear-gradient(110deg, #fbbf24 0%, #f59e0b 30%, #fde68a 50%, #f59e0b 70%, #fbbf24 100%);
        background-size: 220% auto;
        -webkit-background-clip: text; background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: vlShimmer 4.5s linear infinite;
    }
    @keyframes vlShimmer { to { background-position: 220% center; } }

    /* Floating storefront card */
    .vl-float-card { animation: vlCardFloat 7s ease-in-out infinite; }
    @keyframes vlCardFloat { 0%,100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-14px) rotate(-.4deg); } }

    .vl-mini-tile { transition: transform .25s ease, background .25s ease; }
    .vl-mini-tile:hover { transform: translateY(-4px); background: rgba(255,255,255,.16) !important; }

    /* Pulsing live dot */
    .vl-live-dot { position: relative; display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #34d399; }
    .vl-live-dot::after { content: ''; position: absolute; inset: 0; border-radius: 50%; background: #34d399; animation: vlPing 1.8s cubic-bezier(0,0,.2,1) infinite; }
    @keyframes vlPing { 75%,100% { transform: scale(2.6); opacity: 0; } }

    /* ── Logo / trust marquee ────────────────────────────────── */
    .vl-marquee { overflow: hidden; -webkit-mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); }
    .vl-marquee-track { display: flex; gap: 3rem; width: max-content; animation: vlMarquee 28s linear infinite; }
    .vl-marquee:hover .vl-marquee-track { animation-play-state: paused; }
    @keyframes vlMarquee { to { transform: translateX(-50%); } }
    .vl-marquee-item { display: inline-flex; align-items: center; gap: .5rem; white-space: nowrap; color: rgba(255,255,255,.72); font-weight: 600; font-size: .95rem; }

    /* ── Category / pillar hover sheen ───────────────────────── */
    .vl-tile-card { position: relative; overflow: hidden; }
    .vl-tile-card::before {
        content: ''; position: absolute; top: 0; left: -75%; width: 50%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.45), transparent);
        transform: skewX(-20deg); transition: left .65s ease; pointer-events: none;
    }
    .vl-tile-card:hover::before { left: 130%; }
    .vl-tile-card:hover .feature-tile { transform: scale(1.12) rotate(-4deg); }
    .feature-tile { transition: transform .3s cubic-bezier(.34,1.56,.64,1); }

    /* Plan card lift */
    .vl-plan { transition: transform .25s ease, box-shadow .25s ease; }
    .vl-plan:hover { transform: translateY(-8px); box-shadow: var(--vl-shadow-lg) !important; }

    /* Animated counter baseline (avoid layout shift) */
    .vl-counter { font-variant-numeric: tabular-nums; }

    /* CTA aura */
    .vl-cta-aura { animation: vlFloat2 14s ease-in-out infinite; }

    @media (max-width: 575.98px) {
        .vl-hero h1 { font-size: 2.4rem; }
        .section { padding-top: 3rem; padding-bottom: 3rem; }
    }

    /* Respect reduced-motion preferences */
    @media (prefers-reduced-motion: reduce) {
        .reveal, .reveal-stagger > * { opacity: 1 !important; transform: none !important; transition: none !important; }
        .vl-hero, .vl-blob, .vl-shimmer, .vl-float-card, .vl-marquee-track, .vl-live-dot::after, .vl-cta-aura { animation: none !important; }
    }
</style>
@endpush

@section('content')

{{-- ───────────────────────── Hero ───────────────────────── --}}
<section class="vl-hero position-relative overflow-hidden">
    <div class="vl-blob vl-blob-1"></div>
    <div class="vl-blob vl-blob-2"></div>
    <div class="vl-blob vl-blob-3"></div>
    <div class="vl-hero-grid"></div>

    <div class="container position-relative py-5" style="min-height:600px;">
        <div class="row align-items-center g-5 py-4">
            <div class="col-lg-6 reveal reveal-left">
                <span class="badge rounded-pill mb-3 px-3 py-2 fw-semibold glass text-white d-inline-flex align-items-center gap-2">
                    <span class="vl-live-dot"></span> Grow your online presence
                </span>
                <h1 class="display-3 fw-bold text-white lh-1 mb-4">
                    Grow online.<br>
                    Bring buyers &amp; sellers <span class="vl-shimmer">together.</span>
                </h1>
                <p class="fs-5 mb-4" style="color:rgba(255,255,255,.74);max-width:540px;">
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
                <div class="d-flex flex-wrap gap-4 gap-md-5 mt-5 reveal-stagger">
                    @foreach([
                        [$stats['vendors'], '+', 'Verified Vendors'],
                        [$stats['buyers'], '+', 'Happy Buyers'],
                        [100, '%', 'Escrow Protected'],
                    ] as $s)
                    <div class="text-white">
                        <div class="fw-bold fs-3 vl-counter" data-count="{{ $s[0] }}" data-suffix="{{ $s[1] }}">0{{ $s[1] }}</div>
                        <div style="color:rgba(255,255,255,.6)" class="small">{{ $s[2] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block reveal reveal-right">
                <div class="vl-float-card glass rounded-4 p-4 shadow-lg position-relative" style="box-shadow:var(--vl-shadow-lg);">
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
                            <div class="vl-mini-tile p-3 rounded-3 h-100" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);">
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
                            <div class="fw-bold fs-5 text-dark">{{ currency_symbol() }}248,500</div>
                        </div>
                        <span class="btn btn-primary btn-sm">Withdraw <i class="bi bi-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- trust marquee --}}
    <div class="border-top" style="border-color:rgba(255,255,255,.1) !important;background:rgba(0,0,0,.18)">
        <div class="vl-marquee py-3">
            <div class="vl-marquee-track">
                @php $trust = [
                    ['bi-credit-card-2-front','Paystack & Bank Transfer'],
                    ['bi-shield-lock','BVN / NIN Verification'],
                    ['bi-whatsapp','WhatsApp Commerce'],
                    ['bi-cash-stack','Same-day Withdrawals'],
                    ['bi-patch-check','KYC-Verified Vendors'],
                    ['bi-lock','Escrow on Every Deal'],
                    ['bi-truck','Physical & Digital Goods'],
                ]; @endphp
                @foreach(array_merge($trust, $trust) as $t)
                    <span class="vl-marquee-item"><i class="bi {{ $t[0] }} text-warning"></i>{{ $t[1] }}</span>
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
        <div class="text-center mb-5 reveal">
            <span class="eyebrow"><i class="bi bi-compass"></i> Discover</span>
            <h2 class="fw-bold mt-2">Explore the Marketplace</h2>
            <p class="lead-muted">Find exactly what you need across our growing ecosystem.</p>
        </div>
        <div class="row g-3 g-md-4 reveal reveal-stagger">
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
                <a href="{{ route('marketplace.services.index', ['q' => $cat[1]]) }}" class="card vl-tile-card hover-lift text-decoration-none h-100 text-center p-3">
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
        <div class="text-center mb-5 reveal">
            <span class="eyebrow"><i class="bi bi-people"></i> Who it's for</span>
            <h2 class="fw-bold mt-2">Built to grow every kind of seller</h2>
            <p class="lead-muted">From a one-person side hustle to a full agency — establish your online presence and reach more buyers.</p>
        </div>
        <div class="row g-4 reveal reveal-stagger">
            @foreach([
                ['bi-person','Individuals','primary','Freelancers, creators and independent sellers — launch a storefront in minutes and start earning.'],
                ['bi-rocket-takeoff','Startups','success','Validate, sell and collect payments fast, with escrow building buyer trust from day one.'],
                ['bi-building','Companies','info','Give your business a professional online presence and a second sales channel that runs itself.'],
                ['bi-diagram-3','Agencies','warning','Showcase services, manage clients and take bookings — all under your own branded store.'],
            ] as $a)
            <div class="col-6 col-lg-3">
                <div class="card vl-tile-card hover-lift h-100 text-center p-3">
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
        <div class="text-center mt-4 reveal">
            <a href="{{ route('register') }}" class="btn btn-primary px-4">Start growing free <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>

{{-- ──────────────────── How it works ──────────────────── --}}
<section class="section bg-surface border-top">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="eyebrow"><i class="bi bi-signpost-2"></i> How it works</span>
            <h2 class="fw-bold mt-2">Buy and sell with confidence</h2>
            <p class="lead-muted mx-auto" style="max-width:660px;">
                Volamani sits in the middle of every deal. Your payment is held safely in escrow
                until the work is delivered — so buyers never lose money and sellers always get paid.
            </p>
        </div>

        <div class="row g-4 g-lg-4 reveal reveal-stagger">
            {{-- Buyers --}}
            <div class="col-lg-6">
                <div class="card h-100 p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="feature-tile sm bg-primary bg-opacity-10 text-primary"><i class="bi bi-bag-heart"></i></span>
                        <div>
                            <h4 class="fw-bold mb-0">For buyers</h4>
                            <span class="text-muted small">Shop protected, every time</span>
                        </div>
                    </div>
                    @foreach([
                        ['Find what you need','Browse digital products, freelance services and consultations — or post a request and let sellers come to you.'],
                        ['Pay into escrow','Check out with Paystack or bank transfer. Your money is held safely by Volamani — it does not go straight to the seller.'],
                        ['Receive & confirm','Get your delivery and confirm it to release the funds. Not as described? Open a dispute and our team steps in.'],
                    ] as $i => $s)
                    <div class="d-flex gap-3 {{ $i < 2 ? 'mb-3 pb-3 border-bottom' : '' }}">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0 text-white"
                             style="width:34px;height:34px;font-size:.9rem;background:var(--vl-gradient)">{{ $i+1 }}</div>
                        <div>
                            <div class="fw-semibold">{{ $s[0] }}</div>
                            <div class="text-muted small">{{ $s[1] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Sellers --}}
            <div class="col-lg-6">
                <div class="card h-100 p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="feature-tile sm bg-success bg-opacity-10 text-success"><i class="bi bi-shop-window"></i></span>
                        <div>
                            <h4 class="fw-bold mb-0">For sellers</h4>
                            <span class="text-muted small">Get paid for your work</span>
                        </div>
                    </div>
                    @foreach([
                        ['Open your free store','Create a branded storefront and list products, services or bookable consultations in minutes.'],
                        ['Make a sale','Buyers order and pay upfront. The money is secured in escrow, so you know it is really there before you start.'],
                        ['Deliver & get paid','Deliver the work, the escrow releases to your wallet, and you withdraw straight to your bank account.'],
                    ] as $i => $s)
                    <div class="d-flex gap-3 {{ $i < 2 ? 'mb-3 pb-3 border-bottom' : '' }}">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0 text-white"
                             style="width:34px;height:34px;font-size:.9rem;background:var(--vl-gradient)">{{ $i+1 }}</div>
                        <div>
                            <div class="fw-semibold">{{ $s[0] }}</div>
                            <div class="text-muted small">{{ $s[1] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- escrow / trust explainer strip --}}
        <div class="row g-3 mt-4 reveal reveal-stagger">
            @foreach([
                ['bi-shield-lock','Escrow on every deal','Funds are only released once delivery is confirmed.'],
                ['bi-patch-check','Verified vendors','KYC, BVN/NIN and CAC checks keep bad actors out.'],
                ['bi-life-preserver','Disputes & support','A real team mediates fairly when something goes wrong.'],
                ['bi-cash-coin','Fast withdrawals','Move your earnings to your bank account on your schedule.'],
            ] as $e)
            <div class="col-6 col-lg-3">
                <div class="d-flex flex-column align-items-center text-center gap-2 p-3 h-100 rounded-3" style="background:var(--vl-gradient-soft);">
                    <i class="bi {{ $e[0] }} fs-4 text-primary"></i>
                    <div class="fw-semibold small">{{ $e[1] }}</div>
                    <div class="text-muted" style="font-size:.78rem;line-height:1.4">{{ $e[2] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ──────────────────── Pillars ──────────────────── --}}
<section class="section bg-white border-top">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="eyebrow"><i class="bi bi-grid-3x3-gap"></i> One platform</span>
            <h2 class="fw-bold mt-2">Everything your business needs</h2>
            <p class="lead-muted">Shopify + Fiverr + Calendly + Selar — combined and localized.</p>
        </div>
        <div class="row g-4 reveal reveal-stagger">
            @foreach([
                ['bi-bag-check','Digital Products','Buy eBooks, templates, UI kits, music and software with instant delivery.','26,86,219', route('marketplace.products.index')],
                ['bi-briefcase','Freelance Services','Hire vetted talent with packages, requirements, revisions and delivery tracking.','79,70,229', route('marketplace.services.index')],
                ['bi-people','Reverse Requests','Post what you need and qualified sellers send you proposals.','5,150,105', route('marketplace.requests.index')],
                ['bi-calendar2-week','Consultations','Book paid one-on-one sessions with calendar and meeting links.','217,119,6', route('marketplace.consultants.index')],
                ['bi-calculator','Pricing Assistant','Estimate fair project pricing in seconds before you buy or sell.','220,38,38', route('pricing-calculator.index')],
                ['bi-patch-check','Trust, Escrow & KYC','Verified badges, real reviews, escrow on every deal and admin-mediated support.','2,132,199', route('register')],
            ] as $p)
            <div class="col-md-6 col-lg-4">
                <a href="{{ $p[4] }}" class="card vl-tile-card hover-lift h-100 p-2 text-decoration-none text-dark">
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
<section class="section bg-white border-top">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal reveal-left">
                <span class="eyebrow"><i class="bi bi-geo-alt"></i> Built for you</span>
                <h2 class="fw-bold mt-2 mb-4">Built for real<br>business realities</h2>
                <div class="d-flex flex-column gap-3">
                    @foreach([
                        ['bi-bank','Flexible Payments','Pay by card or bank transfer — whatever works for you.'],
                        ['bi-shield-lock','Escrow Protection','Funds held safely until delivery is confirmed.'],
                        ['bi-chat-dots','Direct Messaging','Vendors and buyers can chat and negotiate directly.'],
                        ['bi-award','Verified Vendors','KYC identity and business verification build real trust.'],
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
            <div class="col-lg-6 reveal reveal-right">
                <div class="card shadow border-0 p-4 p-md-5 position-relative overflow-hidden">
                    <div class="position-absolute rounded-circle vl-cta-aura" style="width:200px;height:200px;top:-60px;right:-60px;background:var(--vl-gradient-soft);"></div>
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
<section id="plans" class="section bg-surface border-top" style="scroll-margin-top:80px;">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="eyebrow"><i class="bi bi-stars"></i> Seller plans</span>
            <h2 class="fw-bold mt-2">Plans that grow with you</h2>
            <p class="lead-muted">Lower commission, higher limits and featured placement as you scale. Upgrade anytime.</p>
        </div>
        <div class="row g-3 justify-content-center reveal reveal-stagger mx-auto" style="max-width:960px;">
            @foreach($plans as $plan)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card vl-plan h-100 {{ $plan->is_popular ? 'border-primary shadow' : 'border-0 shadow-sm' }} position-relative">
                    @if($plan->is_popular)
                        <span class="badge bg-primary position-absolute top-0 start-50 translate-middle px-2 py-1 rounded-pill small">Popular</span>
                    @endif
                    <div class="card-body p-3 d-flex flex-column">
                        <h6 class="fw-bold mb-1">{{ $plan->name }}</h6>
                        @if($plan->tagline)<p class="text-muted mb-2" style="font-size:.72rem;line-height:1.3">{{ $plan->tagline }}</p>@endif

                        <div class="mb-2">
                            <span class="fs-4 fw-bold">{{ $plan->isFree() ? 'Free' : money($plan->price) }}</span>
                            @unless($plan->isFree())<span class="text-muted small">{{ $plan->billing_interval->shortLabel() }}</span>@endunless
                        </div>

                        <ul class="list-unstyled mb-3 flex-grow-1" style="font-size:.78rem;">
                            <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>{{ $plan->productLimitLabel() }} products</li>
                            <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>{{ $plan->serviceLimitLabel() }} services</li>
                            @if($plan->commission_rate !== null)
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>{{ rtrim(rtrim(number_format($plan->commission_rate, 2), '0'), '.') }}% commission</li>
                            @endif
                            @if($plan->featured_listing)
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>Featured placement</li>
                            @endif
                            @if($plan->hasTrial())
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>{{ $plan->trial_days }}-day free trial</li>
                            @endif
                            @foreach(($plan->perks ?? []) as $perk)
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>{{ $perk }}</li>
                            @endforeach
                        </ul>

                        @auth
                            <a href="{{ route('vendor.subscription.index') }}" class="btn btn-sm {{ $plan->is_popular ? 'btn-primary' : 'btn-outline-primary' }} w-100">Choose</a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-sm {{ $plan->is_popular ? 'btn-primary' : 'btn-outline-primary' }} w-100">Get started</a>
                        @endauth
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ──────────────────── FAQ ──────────────────── --}}
<section class="section bg-white border-top">
    <div class="container" style="max-width: 820px;">
        <div class="text-center mb-5 reveal">
            <span class="eyebrow"><i class="bi bi-question-circle"></i> Common questions</span>
            <h2 class="fw-bold mt-2">New here? Start with these</h2>
            <p class="lead-muted">The basics of how Volamani keeps your money and your work safe.</p>
        </div>
        <div class="accordion reveal" id="homeFaq">
            @php $homeFaqs = [
                ['What exactly is Volamani?', 'Volamani is an all-in-one marketplace where you can sell and buy digital products, freelance services, expert consultations and physical goods — with a branded storefront and escrow-protected payments built in.'],
                ['How does escrow protect me?', "When a buyer pays, the money is held by Volamani instead of going straight to the seller. It's only released once delivery is confirmed — so buyers are covered if a seller doesn't deliver, and sellers know the funds are real before they start."],
                ['What does it cost to start?', 'Creating an account and opening a storefront is completely free. We only take a small commission when you make a sale, and optional paid plans lower that commission and add features.'],
                ['How do I get paid, and how fast?', 'Completed sales land in your Volamani wallet. Once your account is verified you can withdraw to your bank account whenever you like.'],
                ['What if something goes wrong with an order?', 'Try to sort it out with the other party first. If you can\'t, open a dispute from the order — this freezes the escrowed funds and our support team reviews the evidence and decides a fair outcome.'],
            ]; @endphp
            @foreach($homeFaqs as $i => $faq)
            <div class="accordion-item border-0 mb-2 shadow-sm rounded-3 overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }} fw-semibold" type="button"
                            data-bs-toggle="collapse" data-bs-target="#homeFaq{{ $i }}">
                        {{ $faq[0] }}
                    </button>
                </h2>
                <div id="homeFaq{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#homeFaq">
                    <div class="accordion-body text-muted">{{ $faq[1] }}</div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4 reveal">
            <a href="{{ route('pages.help') }}" class="btn btn-outline-primary px-4">Visit the Help Center <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>

{{-- ──────────────────── CTA ──────────────────── --}}
<section class="section">
    <div class="container">
        <div class="rounded-4 text-center text-white p-5 position-relative overflow-hidden reveal reveal-zoom" style="background:var(--vl-gradient);box-shadow:var(--vl-shadow-lg);">
            <div class="position-absolute rounded-circle vl-cta-aura" style="width:360px;height:360px;top:-140px;left:-80px;background:rgba(255,255,255,.08);"></div>
            <div class="position-absolute rounded-circle vl-cta-aura" style="width:300px;height:300px;bottom:-150px;right:-60px;background:rgba(255,255,255,.08);animation-delay:-4s;"></div>
            <div class="position-relative py-3">
                <h2 class="fw-bold mb-3 text-white">Ready to grow your business?</h2>
                <p class="mb-4 fs-5" style="color:rgba(255,255,255,.85)">Join thousands of entrepreneurs already building on Volamani.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('register') }}" class="btn btn-warning btn-lg fw-bold px-5">Create Free Account</a>
                    <a href="{{ route('marketplace.products.index') }}" class="btn btn-outline-light btn-lg px-4">Browse Marketplace</a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ── Scroll reveal ─────────────────────────────────────── */
    var revealEls = document.querySelectorAll('.reveal');
    if (reduce || !('IntersectionObserver' in window)) {
        revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    } else {
        var io = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
        revealEls.forEach(function (el) { io.observe(el); });
    }

    /* ── Animated counters ─────────────────────────────────── */
    function animateCount(el) {
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        var suffix = el.getAttribute('data-suffix') || '';
        if (reduce || target === 0) { el.textContent = target.toLocaleString() + suffix; return; }
        var dur = 1400, start = performance.now();
        function tick(now) {
            var p = Math.min((now - start) / dur, 1);
            var eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
            el.textContent = Math.round(target * eased).toLocaleString() + suffix;
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }
    var counters = document.querySelectorAll('.vl-counter');
    if (!('IntersectionObserver' in window)) {
        counters.forEach(animateCount);
    } else {
        var cio = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { animateCount(entry.target); obs.unobserve(entry.target); }
            });
        }, { threshold: 0.6 });
        counters.forEach(function (el) { cio.observe(el); });
    }
})();
</script>
@endpush
