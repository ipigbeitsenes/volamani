@extends('layouts.vendor')

@section('title', 'Subscription')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-1">Subscription</h4>
    <p class="text-muted mb-4">Upgrade your plan to unlock more listings, lower commission, and featured placement.</p>

    {{-- Current subscription --}}
    @if($current)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="fw-bold mb-0">{{ $current->plan->name }}</h5>
                            <span class="badge bg-{{ $current->status->badge() }}">{{ $current->status->label() }}</span>
                        </div>
                        <div class="text-muted">
                            {{ $current->plan->priceLabel() }}
                            @if($current->onTrial())
                                · Trial ends {{ $current->trial_ends_at->format('d M Y') }}
                            @elseif($current->ends_at)
                                · {{ $current->isCancelled() ? 'Access until' : 'Renews' }} {{ $current->ends_at->format('d M Y') }}
                                ({{ $current->daysRemaining() }} days left)
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        @unless($current->isCancelled())
                            <form action="{{ route('vendor.subscription.cancel') }}" method="POST"
                                  onsubmit="return confirm('Cancel your subscription? You keep access until the period ends.');">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm">Cancel subscription</button>
                            </form>
                        @else
                            <span class="text-muted small">Auto-renew is off.</span>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">{{ $current ? 'Switch plan' : 'Choose a plan' }}</h6>
        <span class="small text-muted">Wallet balance: <strong>{{ money($wallet->availableBalance()) }}</strong></span>
    </div>

    {{-- Plans --}}
    <div class="row g-3">
        @foreach($plans as $plan)
            @php $isCurrent = $current && $current->plan_id === $plan->id; @endphp
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100 {{ $plan->is_popular ? 'border-primary' : '' }}"
                     @if($plan->is_popular) style="outline:2px solid var(--vl-primary);" @endif>
                    <div class="card-body d-flex flex-column">
                        @if($plan->is_popular)
                            <span class="badge bg-primary align-self-start mb-2">Most popular</span>
                        @endif
                        <h5 class="fw-bold mb-0">{{ $plan->name }}</h5>
                        <p class="text-muted small mb-2">{{ $plan->tagline }}</p>
                        <div class="mb-3">
                            <span class="fs-4 fw-bold">{{ $plan->isFree() ? 'Free' : money($plan->price) }}</span>
                            @unless($plan->isFree())<span class="text-muted">{{ $plan->billing_interval->shortLabel() }}</span>@endunless
                        </div>

                        <ul class="list-unstyled small flex-grow-1">
                            @foreach(($plan->perks ?? []) as $perk)
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>{{ $perk }}</li>
                            @endforeach
                        </ul>

                        @if($isCurrent)
                            <button class="btn btn-outline-secondary w-100" disabled>Current plan</button>
                        @else
                            <form action="{{ route('vendor.subscription.subscribe', $plan) }}" method="POST">
                                @csrf
                                @unless($plan->isFree() || $plan->hasTrial())
                                    <select name="method" class="form-select form-select-sm mb-2">
                                        <option value="wallet">Pay from wallet</option>
                                        <option value="paystack">Pay with Paystack</option>
                                    </select>
                                @endunless
                                <button class="btn btn-primary w-100">
                                    @if($plan->isFree()) Select
                                    @elseif($plan->hasTrial()) Start {{ $plan->trial_days }}-day trial
                                    @else Subscribe @endif
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Billing history --}}
    @if($invoices->isNotEmpty())
        <h6 class="fw-bold mt-4 mb-2">Billing history</h6>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Reference</th><th>Amount</th><th>Method</th><th>Status</th><th>Period</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td><code>{{ $invoice->reference }}</code></td>
                                <td>{{ money($invoice->amount) }}</td>
                                <td class="text-capitalize">{{ $invoice->method ?? '—' }}</td>
                                <td><span class="badge bg-{{ $invoice->status->badge() }}">{{ $invoice->status->label() }}</span></td>
                                <td class="small text-muted">
                                    @if($invoice->period_start){{ $invoice->period_start->format('d M') }} – {{ $invoice->period_end?->format('d M Y') ?? '—' }}@else—@endif
                                </td>
                                <td class="small text-muted">{{ $invoice->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
