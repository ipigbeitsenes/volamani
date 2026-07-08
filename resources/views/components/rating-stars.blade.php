@props([
    'rating' => 0,      // average rating 0–5
    'count' => null,    // number of reviews (null = unknown/hide)
    'size' => 'md',     // sm | md | lg
    'showValue' => true,
    'showCount' => true,
])

@php
    $r = max(0, min(5, (float) $rating));
    $c = $count === null ? null : (int) $count;

    // Round to the nearest half-star for display.
    $full = (int) floor($r);
    $frac = $r - $full;
    $half = $frac >= 0.25 && $frac < 0.75;
    if ($frac >= 0.75) { $full++; }

    $sizeClass = ['sm' => 'vl-stars--sm', 'md' => 'vl-stars--md', 'lg' => 'vl-stars--lg'][$size] ?? 'vl-stars--md';
    $hasReviews = $c === null ? $r > 0 : $c > 0;
@endphp

@once
@push('styles')
<style>
    .vl-stars { display: inline-flex; align-items: center; gap: .38rem; white-space: nowrap; line-height: 1; }
    .vl-stars__icons { display: inline-flex; gap: .06em; color: #f59e0b; }
    .vl-stars__icons .vl-star-empty { color: #d9dfea; }
    .vl-stars--sm { font-size: .8rem; }
    .vl-stars--md { font-size: .95rem; }
    .vl-stars--lg { font-size: 1.2rem; }
    .vl-stars__val { font-weight: 700; color: var(--vl-ink, #0f172a); }
    .vl-stars__count { color: var(--vl-muted, #64748b); }
    .vl-stars--sm .vl-stars__val, .vl-stars--sm .vl-stars__count { font-size: .78rem; }
    @media (prefers-color-scheme: dark) {
        .vl-stars__icons .vl-star-empty { color: #3a4258; }
    }
</style>
@endpush
@endonce

<span {{ $attributes->merge(['class' => "vl-stars $sizeClass"]) }}
      role="img"
      aria-label="Rated {{ number_format($r, 1) }} out of 5{{ $c !== null ? ' from ' . $c . ' reviews' : '' }}">
    <span class="vl-stars__icons" aria-hidden="true">
        @for($i = 1; $i <= 5; $i++)
            @if($i <= $full)
                <i class="bi bi-star-fill"></i>
            @elseif($i === $full + 1 && $half)
                <i class="bi bi-star-half"></i>
            @else
                <i class="bi bi-star vl-star-empty"></i>
            @endif
        @endfor
    </span>
    @if($hasReviews)
        @if($showValue)<span class="vl-stars__val">{{ number_format($r, 1) }}</span>@endif
        @if($showCount && $c !== null)<span class="vl-stars__count">({{ $c }})</span>@endif
    @else
        <span class="vl-stars__count">No reviews yet</span>
    @endif
</span>
