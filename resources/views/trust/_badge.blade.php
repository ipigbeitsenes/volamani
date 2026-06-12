{{-- Vendor trust tier badge. Expects $vendor. --}}
@php($tier = $vendor->trustTier())
<span class="badge bg-{{ $tier->badge() }}-subtle text-{{ $tier->badge() }}" title="Trust score: {{ $vendor->trust_score }}/100">
    <i class="bi {{ $tier->icon() }} me-1"></i>{{ $tier->label() }}
</span>
