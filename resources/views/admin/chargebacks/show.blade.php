@extends('layouts.admin')

@section('title', 'Chargeback ' . $chargeback->reference)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.chargebacks.index') }}">Chargebacks</a></li>
    <li class="breadcrumb-item active">{{ $chargeback->reference }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="fw-bold mb-0">{{ $chargeback->reference }}</h5>
                    <span class="badge bg-{{ $chargeback->status->badge() }} fs-6">{{ $chargeback->status->label() }}</span>
                </div>
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">Vendor</dt><dd class="col-7">{{ $chargeback->vendor->business_name ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Buyer</dt><dd class="col-7">{{ $chargeback->buyer->name ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Disputed amount</dt><dd class="col-7">{{ money($chargeback->amount) }}</dd>
                    <dt class="col-5 text-muted">Clawed back</dt><dd class="col-7">{{ money($chargeback->clawed_back_amount) }}</dd>
                    <dt class="col-5 text-muted">Unrecovered</dt><dd class="col-7 {{ $chargeback->unrecovered_amount > 0 ? 'text-danger' : '' }}">{{ money($chargeback->unrecovered_amount) }}</dd>
                    @if($chargeback->reason)<dt class="col-5 text-muted">Reason</dt><dd class="col-7">{{ $chargeback->reason }}</dd>@endif
                    @if($chargeback->payment)<dt class="col-5 text-muted">Payment</dt><dd class="col-7">{{ $chargeback->payment->reference }}</dd>@endif
                    @if($chargeback->escrow)<dt class="col-5 text-muted">Escrow</dt><dd class="col-7">{{ $chargeback->escrow->reference }} ({{ $chargeback->escrow->status->label() }})</dd>@endif
                    <dt class="col-5 text-muted">Gateway ref</dt><dd class="col-7">{{ $chargeback->gateway_reference ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        @if($chargeback->evidenceNote() || count($chargeback->evidenceFiles()))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Vendor evidence</div>
            <div class="card-body">
                @if($chargeback->evidenceNote())<p class="small mb-2">{{ $chargeback->evidenceNote() }}</p>@endif
                <ul class="small mb-0">
                    @foreach($chargeback->evidenceFiles() as $f)
                        <li>{{ basename($f) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Settle chargeback</div>
            <div class="card-body">
                @if($chargeback->canResolve())
                    <p class="small text-muted">Recording the outcome. <strong>Won</strong> restores held/clawed funds to the vendor. <strong>Lost</strong> refunds the buyer (if still held) and issues the vendor a strike.</p>
                    <form method="POST" action="{{ route('admin.chargebacks.resolve', $chargeback) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Outcome</label>
                            <select name="outcome" class="form-select form-select-sm" required>
                                <option value="won">Won — merchant favour</option>
                                <option value="lost">Lost — buyer favour</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Note</label>
                            <textarea name="note" class="form-control form-control-sm" rows="3" maxlength="2000">{{ old('note') }}</textarea>
                        </div>
                        <button class="btn btn-primary btn-sm w-100">Record outcome</button>
                    </form>
                @else
                    <div class="alert alert-{{ $chargeback->status->badge() }} small mb-0">
                        Resolved {{ optional($chargeback->resolved_at)->diffForHumans() }} — {{ $chargeback->status->label() }}.
                        @if($chargeback->resolution_note)<div class="mt-1">{{ $chargeback->resolution_note }}</div>@endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
