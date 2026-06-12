@extends('layouts.vendor')

@section('title', 'Analytics')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Analytics</h4>
        <p class="text-muted mb-0">How your store is performing.</p>
    </div>

    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['Gross sales', money($stats['gross_sales']), 'bi-cash-stack', 'success'],
                ['Net earnings', money($stats['net_earnings']), 'bi-wallet2', 'primary'],
                ['Paid orders', $stats['paid_orders'] . ' / ' . $stats['total_orders'], 'bi-bag-check', 'info'],
                ['Avg rating', $stats['avg_rating'] . ' (' . $stats['reviews'] . ')', 'bi-star', 'warning'],
            ];
        @endphp
        @foreach($cards as [$label, $value, $icon, $color])
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-{{ $color }} bg-opacity-10 text-{{ $color }}"
                              style="width:48px;height:48px;font-size:1.3rem;"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="h6 fw-bold mb-0">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Earnings — last 14 days</div>
                <div class="card-body">
                    @php $max = max(1, max($trend)); @endphp
                    <div class="d-flex align-items-end gap-1" style="height:160px;">
                        @foreach($trend as $day => $amount)
                            <div class="flex-fill d-flex flex-column justify-content-end align-items-center" title="{{ $day }}: {{ money($amount) }}">
                                <div class="w-100 rounded-top bg-success" style="height: {{ max(2, (int) round($amount / $max * 140)) }}px; opacity:.8;"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between text-muted small mt-2">
                        <span>{{ \Illuminate\Support\Carbon::parse(array_key_first($trend))->format('d M') }}</span>
                        <span>Today</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Top products</div>
                <ul class="list-group list-group-flush">
                    @forelse($topProducts as $p)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">{{ $p->name }}<span class="d-block text-muted">{{ $p->units }} sold</span></span>
                            <span class="fw-semibold">{{ money($p->revenue) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-4 small">No sales yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center"><div class="card-body">
                <div class="h4 fw-bold mb-0">{{ $stats['products'] }}</div><div class="small text-muted">Products</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center"><div class="card-body">
                <div class="h4 fw-bold mb-0">{{ $stats['services'] }}</div><div class="small text-muted">Services</div>
            </div></div>
        </div>
    </div>
</div>
@endsection
