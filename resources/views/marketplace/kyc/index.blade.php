@extends('layouts.account')

@section('title', 'Identity Verification')

@section('content')
<div class="container py-4" style="max-width: 820px;">
    <h4 class="fw-bold mb-1">Identity Verification (KYC)</h4>
    <p class="text-muted mb-4">Verify your identity to unlock withdrawals, selling, and higher trust on Volamani.</p>

    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }}">{{ session($key) }}</div>@endif
    @endforeach

    @if($kyc)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Current status</div>
                    <span class="badge bg-{{ $kyc->status->badge() }}-subtle text-{{ $kyc->status->badge() }} fs-6">{{ $kyc->status->label() }}</span>
                </div>
                <div class="text-end small text-muted">
                    <div>Ref: <span class="font-monospace">{{ $kyc->reference }}</span></div>
                    @if($kyc->submitted_at)<div>Submitted {{ $kyc->submitted_at->format('d M Y') }}</div>@endif
                </div>
            </div>
        </div>
    @endif

    @if($kyc && $kyc->isVerified())
        <div class="alert alert-success">
            <strong>You're verified.</strong> Your identity has been confirmed — all features are unlocked.
        </div>
    @elseif($kyc && $kyc->isPending())
        <div class="alert alert-warning">
            <strong>Under review.</strong> We're checking your documents and will update you within 1–2 business days.
        </div>
    @else
        @if($kyc && $kyc->rejection_reason)
            <div class="alert alert-danger">
                <strong>Your previous submission was rejected.</strong>
                <div class="mt-1">{{ $kyc->rejection_reason }}</div>
                <div class="small mt-1">Please correct the issue and resubmit below.</div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @include('kyc._form', ['action' => route('kyc.submit'), 'kyc' => $kyc])
            </div>
        </div>
    @endif
</div>
@endsection
