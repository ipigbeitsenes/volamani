@extends('layouts.app')

@section('title', 'About Volamani')
@section('meta_description', 'Volamani is a complete digital business ecosystem — sell digital products, services and physical goods with escrow-protected payments.')

@section('content')

{{-- ── Hero ── --}}
<section class="position-relative overflow-hidden text-white py-5" style="background: var(--vl-gradient-dark);">
    <div class="container py-4 position-relative text-center" style="max-width: 760px;">
        <span class="eyebrow text-warning"><i class="bi bi-stars"></i> Our story</span>
        <h1 class="display-5 fw-bold text-white mt-2 mb-3">Empowering entrepreneurs to grow online</h1>
        <p class="fs-5 mb-0" style="color:rgba(255,255,255,.74);">
            Volamani brings buyers and sellers together on one trusted platform — a branded storefront to sell
            digital products, services and physical goods, with escrow protection on every deal.
        </p>
    </div>
</section>

{{-- ── Mission ── --}}
<section class="section bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-bullseye"></i> Mission</span>
                <h2 class="fw-bold mt-2 mb-3">Built for real business realities</h2>
                <p class="lead-muted">
                    Too many talented creators, freelancers and businesses struggle to sell online safely —
                    payments fall through, trust is hard to establish, and generic tools rarely fit how they work.
                </p>
                <p class="lead-muted">
                    Volamani fixes that with flexible payments, KYC-verified vendors, chat-based commerce and escrow
                    that holds funds until delivery is confirmed — so both sides can transact with confidence.
                </p>
                <a href="{{ route('register') }}" class="btn btn-primary mt-2 px-4">Start growing free <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    @foreach([
                        ['bi-shield-check','Escrow Protected','Funds released only on confirmed delivery.','success'],
                        ['bi-patch-check','Verified Vendors','KYC identity and business verification.','primary'],
                        ['bi-bank','Flexible Payments','Card and direct bank transfer.','warning'],
                        ['bi-globe','Global-Ready','Built for the way you do business.','info'],
                    ] as $f)
                    <div class="col-6">
                        <div class="card h-100 p-3 text-center hover-lift">
                            <div class="card-body">
                                <div class="feature-tile mx-auto mb-3 bg-{{ $f[3] }} bg-opacity-10 text-{{ $f[3] }}">
                                    <i class="bi {{ $f[0] }}"></i>
                                </div>
                                <h6 class="fw-bold mb-1">{{ $f[1] }}</h6>
                                <p class="text-muted small mb-0">{{ $f[2] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── What we offer ── --}}
<section class="section bg-surface border-top">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow"><i class="bi bi-grid-3x3-gap"></i> One platform</span>
            <h2 class="fw-bold mt-2">Everything in one place</h2>
            <p class="lead-muted">Shopify + Fiverr + Calendly + Selar — combined and localized.</p>
        </div>
        <div class="row g-4">
            @foreach([
                ['bi-bag-check','Digital Products', route('marketplace.products.index')],
                ['bi-briefcase','Freelance Services', route('marketplace.services.index')],
                ['bi-calendar2-week','Consultations', route('marketplace.consultants.index')],
                ['bi-megaphone','Reverse Requests', route('marketplace.requests.index')],
                ['bi-calculator','Pricing Assistant', route('pricing-calculator.index')],
                ['bi-shop','Vendor Storefronts', route('vendors.index')],
            ] as $o)
            <div class="col-md-6 col-lg-4">
                <a href="{{ $o[2] }}" class="card hover-lift h-100 p-3 text-decoration-none text-dark d-flex flex-row align-items-center gap-3">
                    <div class="feature-tile sm bg-primary bg-opacity-10 text-primary flex-shrink-0"><i class="bi {{ $o[0] }}"></i></div>
                    <span class="fw-semibold">{{ $o[1] }} <i class="bi bi-arrow-right small ms-1 text-muted"></i></span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="section">
    <div class="container">
        <div class="rounded-4 text-center text-white p-5" style="background:var(--vl-gradient);box-shadow:var(--vl-shadow-lg);">
            <h2 class="fw-bold mb-3 text-white">Ready to build with us?</h2>
            <p class="mb-4 fs-5" style="color:rgba(255,255,255,.85)">Join the entrepreneurs already growing on Volamani.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-warning btn-lg fw-bold px-5">Create Free Account</a>
                <a href="{{ route('pages.contact') }}" class="btn btn-outline-light btn-lg px-4">Talk to us</a>
            </div>
        </div>
    </div>
</section>

@endsection
