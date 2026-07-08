@extends('layouts.app')

@section('title', 'Buyer Protection')
@section('meta_description', 'How Volamani protects every purchase — escrow, returns, disputes and chargebacks.')

@section('content')

{{-- ── Hero ── --}}
<section class="position-relative overflow-hidden text-white py-5" style="background: var(--vl-gradient-dark);">
    <div class="container py-3 position-relative">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page">Buyer Protection</li>
            </ol>
        </nav>
        <span class="eyebrow text-warning"><i class="bi bi-shield-check"></i> Guaranteed</span>
        <h1 class="fw-bold text-white mt-2 mb-2">Buyer Protection</h1>
        @if($intro)<p class="mb-0" style="color:rgba(255,255,255,.72);max-width:680px;">{{ $intro }}</p>@endif
    </div>
</section>

{{-- ── Live highlights ── --}}
<section class="section bg-light">
    <div class="container" style="max-width: 960px;">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center p-3">
                    <i class="bi bi-lock fs-3 text-primary mb-2"></i>
                    <div class="fw-bold">Escrow held</div>
                    <div class="small text-muted">Until you receive your order</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center p-3">
                    <i class="bi bi-hourglass-split fs-3 text-primary mb-2"></i>
                    <div class="fw-bold">{{ $escrowDaysMin }}–{{ $escrowDaysMax }} business days</div>
                    <div class="small text-muted">Protection window before payout</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center p-3">
                    <i class="bi bi-arrow-return-left fs-3 text-primary mb-2"></i>
                    <div class="fw-bold">{{ $returnDays }}-day returns</div>
                    <div class="small text-muted">On eligible physical items</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100 text-center p-3">
                    <i class="bi bi-clock-history fs-3 text-primary mb-2"></i>
                    <div class="fw-bold">{{ $responseHours }}h response</div>
                    <div class="small text-muted">Or our team steps in</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Policy sections ── --}}
<section class="section bg-white">
    <div class="container" style="max-width: 820px;">
        @php
            $sections = [
                ['bi-lock',              'Your money is held in escrow', $escrowSummary],
                ['bi-arrow-return-left', 'Returns & refunds',            $returnSummary],
                ['bi-life-preserver',    'Disputes & support',           $disputeProcess],
                ['bi-credit-card-2-front', 'Card chargebacks',           $chargebackNote],
            ];
        @endphp

        @foreach($sections as [$icon, $heading, $body])
            @if($body)
                <div class="mb-4">
                    <h2 class="h5 fw-bold mb-2"><i class="bi {{ $icon }} text-primary me-2"></i>{{ $heading }}</h2>
                    <p class="text-body" style="line-height:1.75;">{{ $body }}</p>
                </div>
            @endif
        @endforeach

        @if($reservePercent > 0)
            <div class="alert alert-light border small">
                <i class="bi bi-piggy-bank text-primary me-1"></i>
                As an extra safeguard, we retain a {{ $reservePercent }}% reserve on seller payouts to cover any late chargebacks, so buyers are always made whole.
            </div>
        @endif

        <div class="alert alert-light border d-flex align-items-start gap-3 mt-4">
            <i class="bi bi-envelope-fill text-primary fs-5"></i>
            <div class="small mb-0">
                Need help with an order? Email <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>,
                or open a support ticket from the order in your account.
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
            <a href="{{ route('pages.legal', 'refunds') }}" class="btn btn-sm rounded-pill btn-light border">Refund Policy</a>
            <a href="{{ route('pages.legal', 'disputes') }}" class="btn btn-sm rounded-pill btn-light border">Dispute Policy</a>
            <a href="{{ route('pages.help') }}" class="btn btn-sm rounded-pill btn-light border">Help Center</a>
        </div>
    </div>
</section>

@endsection
