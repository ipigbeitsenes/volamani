@extends('layouts.app')

@section('title', 'Agree to our Terms')
@section('meta_description', 'Review and accept the Volamani Terms & Conditions to continue.')
@section('robots', 'noindex, nofollow')

@section('content')
@php
    $isVendor = auth()->user()?->isVendor();
@endphp
<div class="container py-5" style="max-width: 760px;">
    <div class="text-center mb-4">
        <div class="feature-tile mx-auto mb-3 bg-gradient-brand text-white"><i class="bi bi-shield-check"></i></div>
        <h1 class="h3 fw-bold">Before you continue</h1>
        <p class="lead-muted">Please review and agree to our Terms &amp; Conditions and policies. This keeps {{ settings('site_name', 'Volamani') }} safe for both buyers and sellers.</p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">The essentials</h6>
            <ul class="list-unstyled mb-0 d-grid gap-2 small">
                <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success mt-1"></i><span><strong>Every purchase is protected.</strong> Buy from verified sellers, and if an order isn't as described you can open a dispute and our team steps in.</span></li>
                <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success mt-1"></i><span><strong>Deal fairly &amp; honestly.</strong> Sellers must deliver as described; buyers must only raise disputes for genuine issues. Abuse on either side leads to strikes and suspension.</span></li>
                <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success mt-1"></i><span><strong>Keep transactions on-platform.</strong> Off-platform deals aren't covered by buyer protection.</span></li>
                <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success mt-1"></i><span><strong>Identity &amp; compliance.</strong> Verification (KYC) may be required to sell and to build trust.</span></li>
            </ul>
        </div>
    </div>

    {{-- Full policy documents --}}
    <div class="row g-2 mb-4">
        @php
            $docs = [
                ['Terms of Service', route('pages.legal', 'terms'), 'bi-file-earmark-text'],
                ['Privacy Policy', route('pages.legal', 'privacy'), 'bi-lock'],
                ['Refunds Policy', route('pages.legal', 'refunds'), 'bi-arrow-counterclockwise'],
                ['Disputes Policy', route('pages.legal', 'disputes'), 'bi-chat-left-text'],
                ['Buyer Protection', route('buyer-protection'), 'bi-shield-check'],
            ];
            if ($isVendor) {
                $docs[] = ['Seller Guide', route('pages.seller-guide'), 'bi-shop'];
            }
        @endphp
        @foreach($docs as [$label, $href, $icon])
            <div class="col-6 col-md-4">
                <a href="{{ $href }}" target="_blank" rel="noopener"
                   class="d-flex align-items-center gap-2 border rounded-3 p-2 text-decoration-none text-body small hover-lift">
                    <i class="bi {{ $icon }} text-primary"></i>
                    <span class="flex-grow-1">{{ $label }}</span>
                    <i class="bi bi-box-arrow-up-right text-muted" style="font-size:.7rem;"></i>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('terms.accept') }}">
                @csrf
                <div class="form-check mb-3">
                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" value="1" required>
                    <label class="form-check-label" for="terms">
                        I have read and agree to the <strong>Terms of Service</strong>, <strong>Privacy Policy</strong>,
                        Refunds &amp; Disputes policies{!! $isVendor ? ' and the <strong>Seller Guide</strong>' : '' !!}.
                    </label>
                    @error('terms')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check2-circle me-1"></i>Agree &amp; Continue
                </button>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link text-muted mt-2">Not now — sign out</button>
            </form>
        </div>
    </div>
</div>
@endsection
