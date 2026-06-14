@extends('layouts.finance')

@section('title', 'Commissions & Fees')

@section('content')
<div class="container-fluid" style="max-width: 640px;">
    <h4 class="fw-bold mb-1">Commissions &amp; Fees</h4>
    <p class="text-muted small mb-4">Platform-wide defaults. Individual vendors or subscription plans may override the commission rate.</p>

    @if($errors->any())
        <div class="alert alert-danger small">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('finance.commissions.update') }}">
        @csrf @method('PUT')

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Platform commission (%)</label>
                    <input type="number" name="platform_commission" class="form-control" min="0" max="100" value="{{ old('platform_commission', $commissions['platform_commission']) }}" required>
                    <div class="form-text">Taken from each marketplace sale.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Affiliate commission (%)</label>
                    <input type="number" name="affiliate_commission" class="form-control" min="0" max="100" value="{{ old('affiliate_commission', $commissions['affiliate_commission']) }}" required>
                    <div class="form-text">Paid to referrers on a referred user's first purchase.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Withdrawal fee (kobo)</label>
                    <input type="number" name="withdrawal_fee" class="form-control" min="0" value="{{ old('withdrawal_fee', $commissions['withdrawal_fee']) }}" required>
                    <div class="form-text">= {{ money($commissions['withdrawal_fee']) }} (100 kobo = ₦1)</div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Minimum withdrawal (kobo)</label>
                    <input type="number" name="min_withdrawal" class="form-control" min="0" value="{{ old('min_withdrawal', $commissions['min_withdrawal']) }}" required>
                    <div class="form-text">= {{ money($commissions['min_withdrawal']) }} (100 kobo = ₦1)</div>
                </div>
            </div>
        </div>

        <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save changes</button>
    </form>
</div>
@endsection
