@extends('layouts.app')

@section('title', 'Help Center')
@section('meta_description', 'Find answers about buying, selling, payments, escrow and disputes on Volamani.')

@section('content')

{{-- ── Header ── --}}
<section class="position-relative overflow-hidden text-white py-5" style="background: var(--vl-gradient-dark);">
    <div class="container py-3 position-relative text-center" style="max-width: 720px;">
        <span class="eyebrow text-warning"><i class="bi bi-life-preserver"></i> Help Center</span>
        <h1 class="fw-bold text-white mt-2 mb-3">How can we help?</h1>
        <p class="mb-0" style="color:rgba(255,255,255,.74);">Browse the topics below or reach our team directly.</p>
    </div>
</section>

{{-- ── Topic cards ── --}}
<section class="section bg-white">
    <div class="container">
        <div class="row g-4">
            @foreach([
                ['bi-bag-check','Buying','How to buy products, services and consultations safely.', route('marketplace.products.index'), 'Browse the marketplace'],
                ['bi-shop','Selling','Set up your storefront and list what you sell.', route('vendor.onboarding'), 'Become a vendor'],
                ['bi-shield-lock','Payments & Escrow','How payments, escrow and withdrawals work.', route('pages.legal', 'refunds'), 'Refund policy'],
                ['bi-life-preserver','Disputes','What to do when an order goes wrong.', route('pages.legal', 'disputes'), 'Dispute policy'],
                ['bi-patch-check','Verification','KYC, BVN/NIN and getting verified.', route('register'), 'Get verified'],
                ['bi-person-badge','Account','Manage your profile, security and notifications.', route('login'), 'Sign in'],
            ] as $t)
            <div class="col-md-6 col-lg-4">
                <div class="card hover-lift h-100 p-3">
                    <div class="card-body">
                        <div class="feature-tile sm bg-primary bg-opacity-10 text-primary mb-3"><i class="bi {{ $t[0] }}"></i></div>
                        <h5 class="fw-bold mb-1">{{ $t[1] }}</h5>
                        <p class="text-muted small mb-3">{{ $t[2] }}</p>
                        <a href="{{ $t[3] }}" class="small fw-semibold text-decoration-none">{{ $t[4] }} <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── FAQ ── --}}
<section class="section bg-surface border-top">
    <div class="container" style="max-width: 820px;">
        <div class="text-center mb-5">
            <span class="eyebrow"><i class="bi bi-question-circle"></i> FAQ</span>
            <h2 class="fw-bold mt-2">Frequently asked questions</h2>
        </div>
        <div class="accordion" id="faqAccordion">
            @php $faqs = [
                ['How does escrow protect me?', 'When you pay, your money is held in escrow — not sent straight to the seller. It is only released once you confirm delivery or the protection window passes, so you are covered if a seller does not deliver.'],
                ['How do I get paid as a seller?', 'Completed sales credit your platform wallet. Once you complete KYC verification you can withdraw your wallet balance to your Nigerian bank account.'],
                ['What payment methods are supported?', 'You can pay with a card or bank transfer through Paystack, or by direct manual bank transfer. All payments stay within the platform so escrow protection applies.'],
                ['What happens if there is a problem with my order?', 'Try to resolve it with the other party first. If you cannot, open a dispute from the order or escrow — this freezes the funds and our team mediates a fair outcome.'],
                ['Is there a fee to sell?', 'Creating an account and a storefront is free. We charge a commission on completed transactions, and optional subscription plans lower your commission and add features.'],
                ['How do I become a verified vendor?', 'Complete the KYC process from your dashboard with your identity details and documents. Once approved you get a verified badge and can withdraw funds.'],
            ]; @endphp
            @foreach($faqs as $i => $faq)
            <div class="accordion-item border-0 mb-2 shadow-sm rounded-3 overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }} fw-semibold" type="button"
                            data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}">
                        {{ $faq[0] }}
                    </button>
                </h2>
                <div id="faq{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted">{{ $faq[1] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <p class="text-muted">Can't find what you're looking for?</p>
            <a href="{{ route('pages.contact') }}" class="btn btn-primary px-4"><i class="bi bi-envelope me-2"></i>Contact Support</a>
        </div>
    </div>
</section>

@endsection
