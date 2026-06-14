@extends('layouts.support')

@section('title', 'KYC ' . $kyc->reference)

@section('content')
<div class="container-fluid">
    <a href="{{ route('support.kyc.index') }}" class="text-decoration-none small">&larr; Back to KYC queue</a>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Applicant Details</span>
                    <span class="badge bg-{{ $kyc->status->badge() }}-subtle text-{{ $kyc->status->badge() }}">{{ $kyc->status->label() }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">User account</dt><dd class="col-sm-8">{{ $kyc->user->name ?? '—' }} ({{ $kyc->user->email ?? '' }})</dd>
                        <dt class="col-sm-4 text-muted">Verification type</dt><dd class="col-sm-8">{{ $kyc->type->label() }}</dd>
                        <dt class="col-sm-4 text-muted">Full legal name</dt><dd class="col-sm-8">{{ $kyc->full_name }}</dd>
                        <dt class="col-sm-4 text-muted">Date of birth</dt><dd class="col-sm-8">{{ $kyc->date_of_birth?->format('d M Y') ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">ID document</dt><dd class="col-sm-8">{{ $kyc->id_type->label() }} — <span class="font-monospace">{{ $kyc->id_number }}</span></dd>
                        @if($kyc->type === \App\Enums\KYCType::Business)
                            <dt class="col-sm-4 text-muted">Business</dt><dd class="col-sm-8">{{ $kyc->business_name }} (RC {{ $kyc->rc_number }})</dd>
                        @endif
                        <dt class="col-sm-4 text-muted">Address</dt><dd class="col-sm-8">{{ collect([$kyc->address, $kyc->city, $kyc->state, $kyc->country])->filter()->join(', ') ?: '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Submitted</dt><dd class="col-sm-8">{{ $kyc->submitted_at?->format('d M Y, g:ia') ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Documents</div>
                <div class="card-body">
                    @php($docs = $kyc->documents())
                    @if(empty($docs))
                        <p class="text-muted mb-0">No documents uploaded.</p>
                    @else
                        <div class="row g-3">
                            @foreach($docs as $field => $path)
                                <div class="col-md-6">
                                    <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                                        <span class="text-capitalize">{{ str_replace('_', ' ', $field) }}</span>
                                        <a href="{{ route('support.kyc.document', [$kyc, $field]) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($kyc->canReview())
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Decision</h6>
                        <form action="{{ route('support.kyc.approve', $kyc) }}" method="POST" class="mb-3"
                              onsubmit="return confirm('Approve and mark this user verified?');">
                            @csrf
                            <button class="btn btn-success w-100">Approve Verification</button>
                        </form>
                        <form action="{{ route('support.kyc.reject', $kyc) }}" method="POST">
                            @csrf
                            <label class="form-label small fw-semibold">Rejection reason</label>
                            <textarea name="reason" rows="3" class="form-control mb-2 @error('reason') is-invalid @enderror" placeholder="Explain what's wrong...">{{ old('reason') }}</textarea>
                            @error('reason')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                            <button class="btn btn-outline-danger w-100">Reject</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body small">
                        <h6 class="fw-semibold mb-2">Review outcome</h6>
                        <p class="mb-1">Reviewed by {{ $kyc->reviewedBy->name ?? '—' }}</p>
                        <p class="mb-1 text-muted">{{ $kyc->reviewed_at?->format('d M Y, g:ia') }}</p>
                        @if($kyc->rejection_reason)
                            <div class="alert alert-danger mt-2 mb-0">{{ $kyc->rejection_reason }}</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
