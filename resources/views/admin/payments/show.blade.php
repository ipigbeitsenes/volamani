@extends('layouts.admin')

@section('title', 'Payment ' . $payment->reference)

@section('content')
<div class="container-fluid" style="max-width: 820px;">
    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-link text-decoration-none mb-3"><i class="bi bi-arrow-left"></i> Back</a>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">{{ $payment->reference }}</span>
                    <span class="badge bg-{{ $payment->status->badge() }}-subtle text-{{ $payment->status->badge() }}">{{ $payment->status->label() }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">User</dt><dd class="col-sm-8">{{ $payment->user->name ?? 'Guest' }}</dd>
                        <dt class="col-sm-4 text-muted">Amount</dt><dd class="col-sm-8 fw-semibold">{{ money($payment->amount) }}</dd>
                        <dt class="col-sm-4 text-muted">Gateway</dt><dd class="col-sm-8">{{ $payment->gateway->label() }}</dd>
                        <dt class="col-sm-4 text-muted">Gateway ref</dt><dd class="col-sm-8">{{ $payment->gateway_reference ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">For</dt><dd class="col-sm-8">{{ $payment->payable_type ? class_basename($payment->payable_type) . ' #' . $payment->payable_id : '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Created</dt><dd class="col-sm-8">{{ $payment->created_at->format('d M Y, H:i') }}</dd>
                        @if($payment->paid_at)<dt class="col-sm-4 text-muted">Paid</dt><dd class="col-sm-8">{{ $payment->paid_at->format('d M Y, H:i') }}</dd>@endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            @php $proof = $payment->bankTransferProof->firstWhere('status', 'pending'); @endphp
            @if($proof)
                <div class="card shadow-sm border-warning-subtle">
                    <div class="card-header bg-white fw-semibold">Bank transfer proof</div>
                    <div class="card-body small">
                        <p class="mb-1"><strong>{{ $proof->bank_name }}</strong></p>
                        <p class="mb-1">{{ $proof->account_name }}</p>
                        <p class="mb-2 text-muted">Claimed {{ money($proof->amount) }}</p>
                        @if($proof->proof_file)
                            <a href="{{ asset('storage/' . $proof->proof_file) }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-2">View proof file</a>
                        @endif
                        <form method="POST" action="{{ route('admin.payments.approve-offline', $payment) }}"
                              onsubmit="return confirm('Approve this bank transfer and fulfil the order?');">
                            @csrf
                            <button class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i>Approve transfer</button>
                        </form>
                    </div>
                </div>
            @endif

            @if($payment->logs->isNotEmpty())
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-white fw-semibold">Webhook log</div>
                    <ul class="list-group list-group-flush small">
                        @foreach($payment->logs as $log)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $log->event }}</span>
                                <span class="text-muted">{{ $log->created_at->format('d M H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
