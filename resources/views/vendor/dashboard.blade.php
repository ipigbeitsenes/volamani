@extends('layouts.vendor')

@section('title', 'Vendor Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

{{-- Pending approval banner --}}
@if($vendor->status->value === 'pending')
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-hourglass-split fs-4 flex-shrink-0"></i>
    <div>
        <strong>Your vendor account is under review.</strong>
        We typically review applications within 24 hours. You can set up your storefront while you wait.
        <a href="{{ route('vendor.storefront') }}" class="alert-link ms-1">Complete your storefront →</a>
    </div>
</div>
@endif

{{-- Orders awaiting the vendor's action --}}
@if(($stats['orders_to_fulfil'] ?? 0) > 0)
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-truck fs-4 flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>{{ $stats['orders_to_fulfil'] }} paid order(s) awaiting fulfilment.</strong>
        Ship them to your buyers — or cancel &amp; refund if you can't deliver to the address.
    </div>
    <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-primary flex-shrink-0">View orders</a>
</div>
@endif

{{-- Page header --}}
<div class="vl-page-head">
    <div>
        <h1>{{ $vendor->business_name }}</h1>
        <p class="vl-sub">{{ $vendor->tagline ?? 'Welcome to your vendor dashboard' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ $vendor->storefront_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-eye me-1"></i>View Storefront
        </a>
        <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Product
        </a>
    </div>
</div>

{{-- Store standing (trust tier, strikes, protection limits) --}}
@php($tier = $vendor->trustTier())
@if($vendor->suspended_for_strikes && $vendor->status->value === 'suspended')
<div class="alert alert-danger d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-exclamation-octagon fs-4 flex-shrink-0"></i>
    <div><strong>Your store is suspended.</strong> It reached our strike threshold. Please contact support about reinstatement.</div>
</div>
@endif
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <span class="badge bg-{{ $tier->badge() }} fs-6"><i class="bi {{ $tier->icon() }} me-1"></i>{{ $tier->label() }}</span>
        <div class="small"><span class="text-muted">Trust score</span> <span class="fw-semibold">{{ $vendor->trust_score }}/100</span></div>
        <div class="small"><span class="text-muted">Strikes</span> <span class="fw-semibold {{ $vendor->strikes > 0 ? 'text-danger' : '' }}">{{ $vendor->strikes }}</span></div>
        <div class="small"><span class="text-muted">Daily payout limit</span> <span class="fw-semibold">{{ $tier->withdrawalCapDaily() === null ? 'Unlimited' : money($tier->withdrawalCapDaily()) }}</span></div>
        <div class="small"><span class="text-muted">Active listings</span> <span class="fw-semibold">{{ $vendor->activeListingCount() }}@if($tier->maxActiveListings() !== null) / {{ $tier->maxActiveListings() }}@endif</span></div>
        <div class="small"><span class="text-muted">Escrow hold</span> <span class="fw-semibold">{{ $tier->escrowReleaseDays() }} business days</span></div>
        <a href="{{ route('buyer-protection') }}" class="btn btn-sm btn-outline-secondary ms-auto">Buyer protection</a>
    </div>
</div>

{{-- Seller guidelines & policies --}}
<div class="mb-4">
    @include('partials.guidelines-card', ['audience' => 'seller'])
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @feature('wallet')
    <div class="col-6 col-lg-3">
        <div class="vl-stat">
            <span class="vl-stat__ico"><i class="bi bi-wallet2"></i></span>
            <div class="vl-stat__label">Wallet Balance</div>
            <div class="vl-stat__value">{{ money($stats['balance']) }}</div>
            <a href="{{ route('vendor.wallet.index') }}" class="vl-stat__foot text-primary fw-semibold">Manage <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    @endfeature
    <div class="col-6 col-lg-3">
        <div class="vl-stat">
            <span class="vl-stat__ico"><i class="bi bi-bag-check"></i></span>
            <div class="vl-stat__label">Total Orders</div>
            <div class="vl-stat__value">{{ number_format($stats['total_orders']) }}</div>
            @if(($stats['orders_to_fulfil'] ?? 0) > 0)
                <a href="{{ route('vendor.orders.index') }}" class="vl-stat__foot text-warning fw-semibold">{{ $stats['orders_to_fulfil'] }} to fulfil <i class="bi bi-arrow-right"></i></a>
            @else
                <a href="{{ route('vendor.orders.index') }}" class="vl-stat__foot text-primary fw-semibold">View all <i class="bi bi-arrow-right"></i></a>
            @endif
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="vl-stat">
            <span class="vl-stat__ico"><i class="bi bi-box-seam"></i></span>
            <div class="vl-stat__label">Products Listed</div>
            <div class="vl-stat__value">{{ number_format($stats['total_products']) }}</div>
            <a href="{{ route('vendor.products.index') }}" class="vl-stat__foot text-primary fw-semibold">Manage <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="vl-stat">
            <span class="vl-stat__ico"><i class="bi bi-star"></i></span>
            <div class="vl-stat__label">Avg Rating</div>
            <div class="vl-stat__value">
                {{ $stats['avg_rating'] > 0 ? $stats['avg_rating'] : '—' }}
                @if($stats['avg_rating'] > 0)<i class="bi bi-star-fill text-warning" style="font-size:1rem;"></i>@endif
            </div>
            <span class="vl-stat__foot text-muted">{{ $stats['total_reviews'] }} reviews</span>
        </div>
    </div>
</div>

{{-- Secondary stats strip --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 p-3 d-flex flex-row align-items-center gap-3"
             style="background:linear-gradient(120deg, rgba(245,158,11,.08), transparent);">
            <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                <i class="bi bi-hourglass-split fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <div class="text-muted small">Pending Earnings (in escrow)</div>
                <div class="fw-bold fs-4">{{ money($stats['pending_earnings']) }}</div>
            </div>
            <a href="{{ route('vendor.wallet.index') }}" class="btn btn-sm btn-outline-warning">View</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 p-3">
            <div class="row text-center g-0">
                <div class="col">
                    <div class="fw-bold fs-5">{{ $stats['total_services'] }}</div>
                    <a href="{{ route('vendor.services.index') }}" class="small text-muted text-decoration-none">Services</a>
                </div>
                <div class="col border-start">
                    <div class="fw-bold fs-5">{{ $stats['service_orders'] }}</div>
                    <a href="{{ route('vendor.orders.index') }}" class="small text-muted text-decoration-none">Service orders</a>
                </div>
                <div class="col border-start">
                    <div class="fw-bold fs-5">{{ number_format($stats['followers']) }}</div>
                    <span class="small text-muted">Followers</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Billing: invoices & contracts --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('vendor.invoices.index') }}" class="card border-0 shadow-sm stat-card h-100 p-3 text-decoration-none" style="border-left-color:#dc2626 !important">
            <div class="text-muted small mb-1">Invoices Outstanding</div>
            <div class="fw-bold fs-5 text-dark">{{ money($documents['outstanding']) }}</div>
            <span class="small text-primary mt-1">View invoices</span>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('vendor.invoices.index') }}" class="card border-0 shadow-sm stat-card h-100 p-3 text-decoration-none" style="border-left-color:#059669 !important">
            <div class="text-muted small mb-1">Invoices Paid</div>
            <div class="fw-bold fs-5 text-dark">{{ money($documents['paid_total']) }}</div>
            <span class="small text-muted mt-1">{{ $documents['draft_count'] }} draft(s)</span>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('vendor.contracts.index') }}" class="card border-0 shadow-sm stat-card h-100 p-3 text-decoration-none" style="border-left-color:#2563eb !important">
            <div class="text-muted small mb-1">Contracts of Sale</div>
            <div class="fw-bold fs-5 text-dark">{{ number_format($documents['contracts']) }}</div>
            <span class="small text-primary mt-1">Manage</span>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('vendor.contracts.create') }}" class="card border-0 shadow-sm h-100 p-3 text-decoration-none d-flex align-items-center justify-content-center text-center"
           style="background:linear-gradient(120deg, rgba(37,99,235,.08), transparent);">
            <i class="bi bi-file-earmark-check text-primary fs-4 mb-1"></i>
            <span class="fw-semibold small text-dark">New Contract of Sale</span>
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Recent orders --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                <h6 class="fw-bold mb-0">Recent Orders</h6>
                <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentOrders as $order)
                <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                    <div class="bg-light rounded p-2 flex-shrink-0">
                        <i class="bi bi-bag-check text-primary fs-5"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-medium text-truncate">Order #{{ $order->reference ?? $order->id }}</div>
                        <div class="text-muted small">{{ $order->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold">{{ money($order->total_amount ?? 0) }}</div>
                        @if(isset($order->status))
                            <span class="badge bg-{{ $order->status->badge() }} small">{{ $order->status->label() }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bag fs-1 d-block mb-2 opacity-25"></i>
                    <p class="mb-2">No orders yet</p>
                    <a href="{{ route('vendor.storefront') }}" class="btn btn-primary btn-sm">Complete your storefront</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="col-lg-4">
        {{-- Store branding (logo / banner) --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Store Branding</h6>
                <a href="{{ route('vendor.storefront') }}" class="small text-muted text-decoration-none">More settings</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('vendor.storefront.branding') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $vendor->logo_url }}" id="dashLogoPreview" class="rounded border bg-white p-1"
                             style="height:64px;width:auto;max-width:170px;object-fit:contain" alt="Logo">
                        <div class="flex-grow-1">
                            <label class="form-label small fw-medium mb-1">Store Logo</label>
                            <input type="file" name="logo" accept="image/*" class="form-control form-control-sm"
                                   onchange="(function(i){if(i.files&&i.files[0]){const r=new FileReader();r.onload=e=>document.getElementById('dashLogoPreview').src=e.target.result;r.readAsDataURL(i.files[0]);}})(this)">
                            <div class="form-text">Square image, max 2MB.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium mb-1">Store Banner</label>
                        <input type="file" name="banner" accept="image/*" class="form-control form-control-sm">
                        <div class="form-text">Recommended 1200×300px, max 5MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-upload me-1"></i>Update branding</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0">Quick Actions</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('vendor.products.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam text-primary"></i>Add New Product
                </a>
                <a href="{{ route('vendor.services.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-briefcase text-primary"></i>Add New Service
                </a>
                <a href="{{ route('vendor.invoices.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-primary"></i>Create Invoice
                </a>
                <a href="{{ route('vendor.contracts.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-check text-primary"></i>Create Contract of Sale
                </a>
                <a href="{{ route('vendor.wallet.withdraw') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between gap-2">
                    <span><i class="bi bi-arrow-up-circle text-success me-2"></i>Request Withdrawal</span>
                    <small class="text-muted">{{ rtrim(rtrim(number_format(config('payment.withdrawal_fee_percent'), 1), '0'), '.') }}% fee</small>
                </a>
                @if($vendor->sellsPhysical())
                <a href="{{ route('vendor.storefront') }}#shipping" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-truck text-primary"></i>Shipping &amp; Delivery Zones
                </a>
                @endif
                <a href="{{ route('vendor.kyc.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-warning"></i>KYC Verification
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-2 small">Your Referral Code</h6>
                <p class="text-muted small mb-2">Earn commission when you refer buyers.</p>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control bg-light"
                           value="{{ url('/register?ref=' . auth()->user()->referral_code) }}"
                           id="refLink" readonly>
                    <button class="btn btn-primary" onclick="copyRef()"><i class="bi bi-clipboard"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyRef() {
    const el = document.getElementById('refLink');
    navigator.clipboard.writeText(el.value);
    event.target.closest('button').innerHTML = '<i class="bi bi-check-lg"></i>';
    setTimeout(() => event.target.closest('button').innerHTML = '<i class="bi bi-clipboard"></i>', 2000);
}
</script>
@endpush
