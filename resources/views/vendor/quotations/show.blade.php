@extends('layouts.vendor')

@section('title', 'Quotation Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('vendor.quotations.index') }}" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> My Quotations
    </a>
    <h4 class="fw-bold mb-0 mt-1">Quotation Details</h4>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                Your Quotation
                <span class="badge bg-{{ $quotation->status->badge() }}">{{ $quotation->status->label() }}</span>
            </div>
            <div class="card-body">
                <dl class="row small">
                    <dt class="col-4 text-muted">Your Price</dt>
                    <dd class="col-8 fw-bold text-primary fs-5">{{ money($quotation->price) }}</dd>

                    <dt class="col-4 text-muted">Delivery</dt>
                    <dd class="col-8">{{ $quotation->delivery_days }} days</dd>

                    <dt class="col-4 text-muted">Submitted</dt>
                    <dd class="col-8">{{ $quotation->created_at->format('M j, Y g:i A') }}</dd>

                    @if($quotation->viewed_at)
                        <dt class="col-4 text-muted">Buyer Viewed</dt>
                        <dd class="col-8">{{ $quotation->viewed_at->format('M j, Y') }}</dd>
                    @endif
                </dl>

                <hr>
                <h6 class="fw-semibold">Your Proposal</h6>
                <p class="text-secondary small lh-lg">{{ $quotation->message }}</p>

                @if($quotation->attachments)
                    <h6 class="fw-semibold">Attachments</h6>
                    @foreach($quotation->attachments as $att)
                        <a href="{{ asset('storage/' . $att) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary me-1 mb-1">
                            <i class="bi bi-download me-1"></i>{{ basename($att) }}
                        </a>
                    @endforeach
                @endif

                @if($quotation->canBeWithdrawn())
                    <div class="mt-3 pt-3 border-top">
                        <form action="{{ route('vendor.quotations.withdraw', $quotation->id) }}" method="POST"
                              onsubmit="return confirm('Withdraw this quotation?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i> Withdraw Quotation
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">The Request</div>
            <div class="card-body">
                <h6 class="fw-bold">{{ $quotation->request->title }}</h6>
                <p class="small text-secondary mb-3">{{ Str::limit($quotation->request->description, 200) }}</p>
                <dl class="row small mb-3">
                    <dt class="col-5 text-muted">Budget</dt>
                    <dd class="col-7">{{ $quotation->request->budgetRange() }}</dd>

                    @if($quotation->request->deadline_at)
                        <dt class="col-5 text-muted">Deadline</dt>
                        <dd class="col-7">{{ $quotation->request->deadline_at->format('M j, Y') }}</dd>
                    @endif

                    <dt class="col-5 text-muted">Status</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $quotation->request->status->badge() }}">
                            {{ $quotation->request->status->label() }}
                        </span>
                    </dd>

                    <dt class="col-5 text-muted">Bids</dt>
                    <dd class="col-7">{{ $quotation->request->quotations_count }}</dd>
                </dl>
                <a href="{{ route('marketplace.requests.show', $quotation->request_id) }}"
                   class="btn btn-outline-primary btn-sm w-100">View Full Request</a>
            </div>
        </div>
    </div>
</div>
@endsection
