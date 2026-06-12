@extends('layouts.admin')

@section('title', 'Withdrawal ' . $withdrawal->reference)

@section('content')
<div class="container-fluid" style="max-width: 760px;">
    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-link text-decoration-none mb-3"><i class="bi bi-arrow-left"></i> Back</a>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">{{ $withdrawal->reference }}</span>
            <span class="badge bg-{{ $withdrawal->status->badge() }}-subtle text-{{ $withdrawal->status->badge() }}">{{ $withdrawal->status->label() }}</span>
        </div>
        <div class="card-body">
            <dl class="row mb-0 small">
                <dt class="col-sm-4 text-muted">User</dt><dd class="col-sm-8">{{ $withdrawal->user->name ?? '—' }} ({{ $withdrawal->user->email ?? '' }})</dd>
                <dt class="col-sm-4 text-muted">Amount</dt><dd class="col-sm-8 fw-semibold">{{ money($withdrawal->amount) }}</dd>
                <dt class="col-sm-4 text-muted">Fee</dt><dd class="col-sm-8">{{ money($withdrawal->fee) }}</dd>
                <dt class="col-sm-4 text-muted">Net payout</dt><dd class="col-sm-8 fw-semibold">{{ money($withdrawal->net_amount) }}</dd>
                <dt class="col-sm-4 text-muted">Bank</dt><dd class="col-sm-8">{{ $withdrawal->bank_name }}</dd>
                <dt class="col-sm-4 text-muted">Account</dt><dd class="col-sm-8">{{ $withdrawal->account_name }} — {{ $withdrawal->account_number }}</dd>
                <dt class="col-sm-4 text-muted">Requested</dt><dd class="col-sm-8">{{ $withdrawal->created_at->format('d M Y, H:i') }}</dd>
                @if($withdrawal->processed_at)
                    <dt class="col-sm-4 text-muted">Processed</dt><dd class="col-sm-8">{{ $withdrawal->processed_at->format('d M Y, H:i') }} by {{ $withdrawal->processedBy->name ?? '—' }}</dd>
                @endif
                @if($withdrawal->admin_notes)
                    <dt class="col-sm-4 text-muted">Notes</dt><dd class="col-sm-8">{{ $withdrawal->admin_notes }}</dd>
                @endif
            </dl>
        </div>
    </div>

    @if($withdrawal->canBeProcessed())
        <div class="card shadow-sm border-primary-subtle">
            <div class="card-header bg-white fw-semibold">Process request</div>
            <div class="card-body d-flex flex-wrap gap-2 align-items-start">
                <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}"
                      onsubmit="return confirm('Approve and pay out this withdrawal?');">
                    @csrf
                    <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve &amp; pay</button>
                </form>
                <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" class="flex-grow-1">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="reason" class="form-control" placeholder="Reason for rejection" required>
                        <button class="btn btn-outline-danger">Reject &amp; refund</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
