@extends('layouts.support')

@section('title', 'Support Dashboard')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-1">Support Desk</h4>
    <p class="text-muted small mb-4">Resolve buyer & vendor issues — tickets, returns and verifications.</p>

    {{-- Work queues --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['Open tickets', $queues['disputes_open'], route('support.disputes.index'), 'bi-life-preserver', 'danger'],
                ['Pending returns', $queues['returns_pending'], route('support.returns.index'), 'bi-arrow-return-left', 'warning'],
                ['KYC to review', $queues['kyc_pending'], route('support.kyc.index'), 'bi-shield-check', 'info'],
                ['Tickets today', $queues['disputes_today'], route('support.disputes.index'), 'bi-calendar-day', 'secondary'],
            ];
        @endphp
        @foreach($cards as [$label, $count, $url, $icon, $color])
            <div class="col-6 col-xl-3">
                <a href="{{ $url }}" class="card shadow-sm h-100 text-decoration-none">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-{{ $color }} bg-opacity-10 text-{{ $color }}"
                              style="width:48px;height:48px;font-size:1.3rem;"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="h4 fw-bold mb-0 {{ $count > 0 ? 'text-dark' : 'text-muted' }}">{{ number_format($count) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        {{-- Recent tickets --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    Recent support tickets
                    <a href="{{ route('support.disputes.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($queues['recent_disputes'] as $d)
                        <a href="{{ route('support.disputes.show', $d) }}" class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">
                                <span class="fw-semibold d-block font-monospace">{{ $d->reference }}</span>
                                <span class="text-muted">{{ $d->buyer->name ?? '—' }} vs {{ $d->vendor->business_name ?? '—' }}</span>
                            </span>
                            <span class="badge bg-{{ $d->status->badge() }}-subtle text-{{ $d->status->badge() }}">{{ $d->status->label() }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted text-center py-4 small">No tickets yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pending KYC --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    KYC awaiting review
                    <a href="{{ route('support.kyc.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($queues['recent_kyc'] as $k)
                        <a href="{{ route('support.kyc.show', $k) }}" class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">
                                <span class="fw-semibold d-block">{{ $k->full_name }}</span>
                                <span class="text-muted">{{ $k->user->email ?? '' }}</span>
                            </span>
                            <span class="badge bg-{{ $k->status->badge() }}-subtle text-{{ $k->status->badge() }}">{{ $k->status->label() }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted text-center py-4 small">Nothing to review.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
