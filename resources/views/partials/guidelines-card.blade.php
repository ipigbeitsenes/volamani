{{--
    Guidelines & policies card for dashboards.
    Usage: @include('partials.guidelines-card', ['audience' => 'seller'|'buyer'])
--}}
@php
    $audience = $audience ?? 'buyer';
    $isSeller = $audience === 'seller';
    $vlUser = auth()->user();

    $points = $isSeller
        ? [
            'List honestly — describe items accurately and price transparently.',
            'Fulfil fast — ship or deliver promptly; funds release from escrow once the buyer receives the item.',
            'Deliver as described — lost disputes and chargebacks add strikes; enough strikes suspend your store.',
            'Keep deals on-platform — off-platform sales aren\'t protected by escrow.',
        ]
        : [
            'Only pay through Volamani — escrow protects payments made on-platform.',
            'Raise disputes for genuine issues only — you have 24 hours after a digital purchase to open a ticket.',
            'Abuse has consequences — rejected disputes and reversed chargebacks add strikes and can restrict your account.',
            'Confirm receipt honestly so sellers are paid fairly.',
        ];

    $links = $isSeller
        ? [
            ['Seller Guide', route('pages.seller-guide'), 'bi-shop'],
            ['Buyer Protection', route('buyer-protection'), 'bi-shield-check'],
            ['Terms of Service', route('pages.legal', 'terms'), 'bi-file-earmark-text'],
            ['Disputes Policy', route('pages.legal', 'disputes'), 'bi-chat-left-text'],
        ]
        : [
            ['Buyer Protection', route('buyer-protection'), 'bi-shield-check'],
            ['Help Centre', route('pages.help'), 'bi-question-circle'],
            ['Terms of Service', route('pages.legal', 'terms'), 'bi-file-earmark-text'],
            ['Refunds Policy', route('pages.legal', 'refunds'), 'bi-arrow-counterclockwise'],
        ];
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle text-primary me-2"></i>{{ $isSeller ? 'Seller' : 'Buyer' }} Guidelines &amp; Policies</h6>
    </div>
    <div class="card-body">
        <ul class="list-unstyled d-grid gap-2 small mb-3">
            @foreach($points as $p)
                <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i><span>{{ $p }}</span></li>
            @endforeach
        </ul>

        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($links as [$label, $href, $icon])
                <a href="{{ $href }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                </a>
            @endforeach
        </div>

        @if($vlUser?->terms_accepted_at)
            <div class="small text-muted border-top pt-2">
                <i class="bi bi-patch-check-fill text-success me-1"></i>
                You accepted our Terms on {{ $vlUser->terms_accepted_at->format('j M Y') }}.
            </div>
        @endif
    </div>
</div>
