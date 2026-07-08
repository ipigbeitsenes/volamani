@extends('layouts.vendor')

@section('title', 'Chargeback ' . $chargeback->reference)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vendor.chargebacks.index') }}">Chargebacks</a></li>
    <li class="breadcrumb-item active">{{ $chargeback->reference }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-0">{{ $chargeback->reference }}</h5>
                        <div class="small text-muted">Opened {{ $chargeback->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="badge bg-{{ $chargeback->status->badge() }} fs-6">{{ $chargeback->status->label() }}</span>
                </div>

                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">Disputed amount</dt><dd class="col-7">{{ money($chargeback->amount) }}</dd>
                    <dt class="col-5 text-muted">Recovered from you</dt><dd class="col-7">{{ money($chargeback->clawed_back_amount) }}</dd>
                    @if($chargeback->reason)<dt class="col-5 text-muted">Reason</dt><dd class="col-7">{{ $chargeback->reason }}</dd>@endif
                    @if($chargeback->payment)<dt class="col-5 text-muted">Payment</dt><dd class="col-7">{{ $chargeback->payment->reference }}</dd>@endif
                    @if($chargeback->escrow)<dt class="col-5 text-muted">Escrow</dt><dd class="col-7">{{ $chargeback->escrow->reference }}</dd>@endif
                </dl>

                @if($chargeback->status->value === 'won')
                    <div class="alert alert-success mt-3 mb-0 small"><i class="bi bi-check-circle me-1"></i>You won this chargeback. Any held or recovered funds have been restored.</div>
                @elseif($chargeback->status->value === 'lost')
                    <div class="alert alert-danger mt-3 mb-0 small"><i class="bi bi-x-circle me-1"></i>This chargeback was lost. The buyer was refunded and a strike was recorded.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Contest with evidence</div>
            <div class="card-body">
                @if($chargeback->canContest())
                    <p class="small text-muted">Upload proof the order was legitimate — delivery confirmation, chat logs, signed receipts. This is forwarded to the bank.</p>
                    <form method="POST" action="{{ route('vendor.chargebacks.contest', $chargeback) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Your account of the sale <span class="text-danger">*</span></label>
                            <textarea name="note" class="form-control form-control-sm @error('note') is-invalid @enderror" rows="4" maxlength="2000" required>{{ old('note') }}</textarea>
                            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Evidence files (JPG/PNG/PDF, max 5)</label>
                            <input type="file" name="files[]" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" multiple>
                        </div>
                        <button class="btn btn-primary btn-sm w-100"><i class="bi bi-upload me-1"></i>Submit evidence</button>
                    </form>
                @else
                    @php $note = $chargeback->evidenceNote(); @endphp
                    @if($note)
                        <p class="small text-muted mb-1">Evidence submitted:</p>
                        <p class="small mb-2">{{ $note }}</p>
                        <ul class="small mb-0">
                            @foreach($chargeback->evidenceFiles() as $f)
                                <li>{{ basename($f) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="small text-muted mb-0">This chargeback can no longer be contested.</p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
