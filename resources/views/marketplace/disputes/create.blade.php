@extends('layouts.app')

@section('title', 'Open a Support Ticket')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <a href="{{ route('escrows.show', $escrow) }}" class="text-decoration-none small">&larr; Back to escrow</a>

    <h4 class="fw-bold mt-3 mb-1">Open a Support Ticket</h4>
    <p class="text-muted mb-4">
        For {{ class_basename($escrow->escrowable_type) }}
        <span class="font-monospace">{{ $escrow->escrowable?->reference }}</span>
        · {{ money($escrow->total_amount) }} held in escrow.
    </p>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('disputes.store', $escrow) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">What's the problem?</label>
                    <select name="reason" class="form-select @error('reason') is-invalid @enderror">
                        <option value="">Select a reason…</option>
                        @foreach(\App\Enums\DisputeReason::cases() as $reason)
                            <option value="{{ $reason->value }}" @selected(old('reason') === $reason->value)>{{ $reason->label() }}</option>
                        @endforeach
                    </select>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Describe the issue</label>
                    <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror"
                              placeholder="Explain what went wrong and what outcome you're seeking…">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Evidence <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                    <div class="form-text">Screenshot, document or zip — max 5MB.</div>
                    @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="alert alert-warning small">
                    Opening a support ticket freezes the funds in escrow until our support team
                    reviews it with the seller and resolves it.
                </div>

                <button class="btn btn-danger">Open Support Ticket</button>
                <a href="{{ route('escrows.show', $escrow) }}" class="btn btn-link text-muted">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
