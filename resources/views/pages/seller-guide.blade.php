@extends('layouts.app')

@section('title', 'Seller Guide')
@section('meta_description', 'Everything you need to start selling on Volamani — set up your store, list products and get paid securely.')

@section('content')

{{-- ── Header ── --}}
<section class="position-relative overflow-hidden text-white py-5" style="background: var(--vl-gradient-dark);">
    <div class="container py-3 position-relative text-center" style="max-width: 720px;">
        <span class="eyebrow text-warning"><i class="bi bi-rocket-takeoff"></i> Seller Guide</span>
        <h1 class="fw-bold text-white mt-2 mb-3">Start selling on Volamani</h1>
        <p class="mb-0" style="color:rgba(255,255,255,.74);">From sign-up to your first payout — here's how it works.</p>
    </div>
</section>

{{-- ── Steps ── --}}
<section class="section bg-white">
    <div class="container" style="max-width: 880px;">
        <div class="d-flex flex-column gap-4">
            @foreach([
                ['Create your free account','Sign up in under a minute. No upfront fees — you only pay a commission when you make a sale.'],
                ['Set up your branded storefront','Add your business name, logo, banner and WhatsApp number. Your store gets its own shareable link.'],
                ['Get verified (KYC)','Complete identity and business verification to earn a verified badge and build buyer trust.'],
                ['List products, services or consultations','Add digital downloads, freelance service packages, bookable consultations or physical goods — with prices, images and details.'],
                ['Receive orders','Buyers order and pay by card, bank transfer or on delivery — building trust on both sides.'],
                ['Get paid','You get paid for every completed sale.'],
            ] as $i => $step)
            <div class="d-flex gap-3 gap-md-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0 text-white"
                     style="width:46px;height:46px;background:var(--vl-gradient);">{{ $i + 1 }}</div>
                <div class="pt-1">
                    <h3 class="h5 fw-bold mb-1">{{ $step[0] }}</h3>
                    <p class="text-muted mb-0">{{ $step[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Tips ── --}}
<section class="section bg-surface border-top">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow"><i class="bi bi-lightbulb"></i> Pro tips</span>
            <h2 class="fw-bold mt-2">Sell more, faster</h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['bi-camera','Use clear visuals','Quality images and previews build buyer confidence and increase conversions.'],
                ['bi-stars','Earn great reviews','Deliver on time and communicate well — strong reviews lift your trust score and ranking.'],
                ['bi-chat-dots','Respond quickly','Fast replies on WhatsApp and order messages close more deals.'],
                ['bi-graph-up-arrow','Upgrade your plan','Paid plans lower your commission and unlock featured placement as you scale.'],
            ] as $tip)
            <div class="col-md-6 col-lg-3">
                <div class="card hover-lift h-100 p-3 text-center">
                    <div class="card-body">
                        <div class="feature-tile mx-auto mb-3 bg-primary bg-opacity-10 text-primary"><i class="bi {{ $tip[0] }}"></i></div>
                        <h6 class="fw-bold mb-1">{{ $tip[1] }}</h6>
                        <p class="text-muted small mb-0">{{ $tip[2] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="section">
    <div class="container">
        <div class="rounded-4 text-center text-white p-5" style="background:var(--vl-gradient);box-shadow:var(--vl-shadow-lg);">
            <h2 class="fw-bold mb-3 text-white">Ready to open your store?</h2>
            <p class="mb-4 fs-5" style="color:rgba(255,255,255,.85)">It's free to start. List your first product in minutes.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-warning btn-lg fw-bold px-5">Start Selling Free</a>
                <a href="{{ route('pages.help') }}" class="btn btn-outline-light btn-lg px-4">Visit Help Center</a>
            </div>
        </div>
    </div>
</section>

@endsection
