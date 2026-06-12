{{-- Star rating display. Expects $rating (0–5 float); optional $count. --}}
@php($r = round((float) ($rating ?? 0)))
<span class="text-warning" title="{{ number_format((float)($rating ?? 0), 1) }} / 5">
    @for($i = 1; $i <= 5; $i++)
        <i class="bi {{ $i <= $r ? 'bi-star-fill' : 'bi-star' }}"></i>
    @endfor
</span>
@isset($count)
    <span class="text-muted small ms-1">({{ $count }})</span>
@endisset
