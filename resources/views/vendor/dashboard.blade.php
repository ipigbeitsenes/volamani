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

{{-- Welcome --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-0">{{ $vendor->business_name }}</h4>
        <p class="text-muted mb-0 small">{{ $vendor->tagline ?? 'Welcome to your vendor dashboard' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ $vendor->storefront_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-eye me-1"></i>View Storefront
        </a>
        <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Product
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm stat-card h-100 p-3">
            <div class="text-muted small mb-1">Wallet Balance</div>
            <div class="fw-bold fs-4">{{ money($stats['balance']) }}</div>
            <a href="{{ route('vendor.wallet.index') }}" class="small text-primary mt-1">Manage</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm stat-card h-100 p-3" style="border-left-color:#059669 !important">
            <div class="text-muted small mb-1">Total Orders</div>
            <div class="fw-bold fs-4">{{ $stats['total_orders'] }}</div>
            <a href="{{ route('vendor.orders.index') }}" class="small text-primary mt-1">View all</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm stat-card h-100 p-3" style="border-left-color:#f59e0b !important">
            <div class="text-muted small mb-1">Products Listed</div>
            <div class="fw-bold fs-4">{{ $stats['total_products'] }}</div>
            <a href="{{ route('vendor.products.index') }}" class="small text-primary mt-1">Manage</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm stat-card h-100 p-3" style="border-left-color:#8b5cf6 !important">
            <div class="text-muted small mb-1">Avg Rating</div>
            <div class="fw-bold fs-4">
                {{ $stats['avg_rating'] > 0 ? $stats['avg_rating'] : '—' }}
                @if($stats['avg_rating'] > 0)<i class="bi bi-star-fill text-warning small"></i>@endif
            </div>
            <span class="small text-muted">{{ $stats['total_reviews'] }} reviews</span>
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
                <a href="{{ route('vendor.wallet.withdraw') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-up-circle text-success"></i>Request Withdrawal
                </a>
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
