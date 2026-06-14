@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Platform Overview</h4>

    {{-- Headline stats --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['Total users', number_format($stats['users']), 'bi-people', 'primary'],
                ['Active vendors', number_format($stats['vendors_active']), 'bi-shop', 'success'],
                ['Total orders', number_format($stats['orders']), 'bi-bag-check', 'info'],
                ['Gross revenue', money($stats['revenue']), 'bi-cash-stack', 'dark'],
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

    {{-- Work queues --}}
    <h6 class="text-uppercase text-muted small fw-bold mb-3">Needs attention</h6>
    <div class="row g-3 mb-4">
        @php
            $queues = [
                ['Vendor approvals', $stats['queues']['vendors'], route('admin.vendors.index', ['status' => 'pending']), 'bi-shop'],
                ['KYC reviews', $stats['queues']['kyc'], route('admin.kyc.index'), 'bi-shield-check'],
                ['Withdrawals', $stats['queues']['withdrawals'], route('admin.withdrawals.index'), 'bi-arrow-up-circle'],
                ['Product reviews', $stats['queues']['products'], route('admin.products.index'), 'bi-box-seam'],
                ['Open disputes', $stats['queues']['disputes'], route('admin.disputes.index'), 'bi-exclamation-triangle'],
                ['Bank transfers', $stats['queues']['bank_transfers'], route('admin.payments.index'), 'bi-bank'],
                ['Returns', $stats['queues']['returns'] ?? 0, route('admin.returns.index'), 'bi-arrow-return-left'],
                ['Category requests', $stats['queues']['category_requests'] ?? 0, route('admin.category-requests.index'), 'bi-tags'],
            ];
        @endphp
        @foreach($queues as [$label, $count, $url, $icon])
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ $url }}" class="card shadow-sm h-100 text-decoration-none">
                    <div class="card-body text-center">
                        <i class="bi {{ $icon }} fs-4 text-muted"></i>
                        <div class="h4 fw-bold mb-0 mt-2 {{ $count > 0 ? 'text-danger' : 'text-dark' }}">{{ $count }}</div>
                        <div class="small text-muted">{{ $label }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        {{-- Revenue sparkline (CSS bars, no JS lib) --}}
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Revenue — last 14 days</div>
                <div class="card-body">
                    @php $max = max(1, max($revenue)); @endphp
                    <div class="d-flex align-items-end gap-1" style="height:160px;">
                        @foreach($revenue as $day => $amount)
                            <div class="flex-fill d-flex flex-column justify-content-end align-items-center" title="{{ $day }}: {{ money($amount) }}">
                                <div class="w-100 rounded-top bg-primary" style="height: {{ max(2, (int) round($amount / $max * 140)) }}px; opacity:.8;"></div>
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

        {{-- Recent payments --}}
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    Recent payments
                    <a href="{{ route('admin.payments.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($stats['recent_payments'] as $p)
                        <a href="{{ route('admin.payments.show', $p) }}" class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">
                                <span class="fw-semibold d-block">{{ $p->user->name ?? 'Guest' }}</span>
                                <span class="text-muted">{{ $p->reference }}</span>
                            </span>
                            <span class="text-end">
                                <span class="fw-semibold d-block">{{ money($p->amount) }}</span>
                                <span class="badge bg-{{ $p->status->badge() }}-subtle text-{{ $p->status->badge() }}">{{ $p->status->label() }}</span>
                            </span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted text-center py-4 small">No payments yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
