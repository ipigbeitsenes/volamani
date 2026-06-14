@extends('layouts.finance')

@section('title', 'Finance Dashboard')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-1">Finance Overview</h4>
    <p class="text-muted small mb-4">Money movement across the platform — revenue, escrow, payouts and fees.</p>

    {{-- Headline figures --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['Gross revenue', money($stats['gross_revenue']), 'bi-cash-stack', 'success'],
                ['Held in escrow', money($stats['escrow_held']), 'bi-safe2', 'warning'],
                ['Pending payouts', money($stats['withdrawals_pending_sum']), 'bi-arrow-up-circle', 'danger'],
                ['Bank transfers to check', number_format($stats['bank_transfers']), 'bi-bank', 'info'],
            ];
        @endphp
        @foreach($cards as [$label, $value, $icon, $color])
            <div class="col-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-{{ $color }} bg-opacity-10 text-{{ $color }}"
                              style="width:48px;height:48px;font-size:1.3rem;"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="h5 fw-bold mb-0">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Queue shortcuts --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <a href="{{ route('finance.withdrawals.index', ['status' => 'pending']) }}" class="card shadow-sm text-decoration-none">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-arrow-up-circle text-danger me-2"></i>Withdrawals awaiting approval</span>
                    <span class="badge bg-danger">{{ number_format($stats['withdrawals_pending']) }}</span>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('finance.payments.index') }}" class="card shadow-sm text-decoration-none">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bank text-info me-2"></i>Bank transfers to verify</span>
                    <span class="badge bg-info">{{ number_format($stats['bank_transfers']) }}</span>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3">
        {{-- Revenue trend --}}
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Revenue — last 14 days</div>
                <div class="card-body">
                    @php $max = max(1, max($revenue)); @endphp
                    <div class="d-flex align-items-end gap-1" style="height:160px;">
                        @foreach($revenue as $day => $amount)
                            <div class="flex-fill d-flex flex-column justify-content-end align-items-center" title="{{ $day }}: {{ money($amount) }}">
                                <div class="w-100 rounded-top bg-success" style="height: {{ max(2, (int) round($amount / $max * 140)) }}px; opacity:.8;"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between text-muted small mt-2">
                        <span>{{ \Illuminate\Support\Carbon::parse(array_key_first($revenue))->format('d M') }}</span>
                        <span>Today</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent withdrawals --}}
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    Recent withdrawals
                    <a href="{{ route('finance.withdrawals.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($stats['recent_withdrawals'] as $w)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">
                                <span class="fw-semibold d-block">{{ $w->user->name ?? '—' }}</span>
                                <span class="text-muted">{{ $w->reference }}</span>
                            </span>
                            <span class="text-end">
                                <span class="fw-semibold d-block">{{ money($w->amount) }}</span>
                                <span class="badge bg-{{ $w->status->badge() }}-subtle text-{{ $w->status->badge() }}">{{ $w->status->label() }}</span>
                            </span>
                        </div>
                    @empty
                        <div class="list-group-item text-muted text-center py-4 small">No withdrawals yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
