@extends('layouts.admin')

@section('title', 'Dispute ' . $dispute->reference)

@section('content')
@php($escrow = $dispute->escrow)
<div class="container-fluid py-4">
    <a href="{{ route('admin.disputes.index') }}" class="text-decoration-none small">&larr; Back to disputes</a>

    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }} mt-3">{{ session($key) }}</div>@endif
    @endforeach

    <div class="row g-4 mt-1">
        {{-- Left: details + conversation --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 font-monospace">{{ $dispute->reference }}</h5>
                            <p class="text-muted mb-0 small">
                                {{ $dispute->reason->label() }} ·
                                opened by {{ $dispute->raisedBy->name ?? '—' }}
                                {{ $dispute->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="badge bg-{{ $dispute->status->badge() }}-subtle text-{{ $dispute->status->badge() }} fs-6">
                            {{ $dispute->status->label() }}
                        </span>
                    </div>

                    <div class="row small border-top pt-3">
                        <div class="col-md-4"><span class="text-muted">Buyer:</span> {{ $dispute->buyer->name ?? '—' }}</div>
                        <div class="col-md-4"><span class="text-muted">Vendor:</span> {{ $dispute->vendor->business_name ?? '—' }}</div>
                        <div class="col-md-4"><span class="text-muted">Escrow:</span> <span class="font-monospace">{{ $escrow?->reference }}</span></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Conversation</div>
                <div class="card-body">
                    @foreach($dispute->messages as $msg)
                        @if($msg->is_system)
                            <div class="text-center text-muted small my-2">{{ $msg->message }}</div>
                        @else
                            <div class="d-flex mb-3">
                                <div class="p-3 rounded-3 {{ $msg->is_staff ? 'bg-warning-subtle' : 'bg-light' }}" style="max-width: 80%;">
                                    <div class="small fw-semibold mb-1">
                                        {{ $msg->is_staff ? 'Support Team' : ($msg->sender?->name ?? 'User') }}
                                        <span class="text-muted fw-normal">· {{ $msg->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div>{{ $msg->message }}</div>
                                    @if($msg->attachment)
                                        <a href="{{ $msg->attachmentUrl() }}" target="_blank" class="small d-inline-block mt-1">📎 {{ $msg->attachment_name ?? 'Attachment' }}</a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if($dispute->isOpen())
                    <div class="card-footer bg-white">
                        <form action="{{ route('admin.disputes.message', $dispute) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <textarea name="message" rows="2" class="form-control mb-2" placeholder="Reply as support…">{{ old('message') }}</textarea>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="file" name="attachment" class="form-control form-control-sm w-auto">
                                <button class="btn btn-secondary btn-sm">Send Staff Reply</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: escrow + resolution --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Escrow</div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between"><span class="text-muted">Total</span><span>{{ money($escrow?->total_amount ?? 0) }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Vendor earnings</span><span>{{ money($escrow?->vendor_earnings ?? 0) }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Held now</span><span class="text-warning">{{ money($escrow?->heldAmount() ?? 0) }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Status</span>
                        <span class="badge bg-{{ $escrow?->status->badge() }}-subtle text-{{ $escrow?->status->badge() }}">{{ $escrow?->status->label() }}</span>
                    </div>
                </div>
            </div>

            @if($dispute->isResolved())
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2">Resolution</h6>
                        <span class="badge bg-{{ $dispute->resolution?->badge() }}-subtle text-{{ $dispute->resolution?->badge() }}">
                            {{ $dispute->resolution?->label() }}
                        </span>
                        @if($dispute->resolution_note)<p class="small text-muted mt-2 mb-0">{{ $dispute->resolution_note }}</p>@endif
                        <p class="small text-muted mt-2 mb-0">By {{ $dispute->resolvedBy->name ?? '—' }} · {{ $dispute->resolved_at?->format('d M Y') }}</p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Resolve</h6>
                        <form action="{{ route('admin.disputes.resolve', $dispute) }}" method="POST"
                              onsubmit="return confirm('Apply this resolution? Funds will be settled immediately.');">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Outcome</label>
                                <select name="resolution" class="form-select form-select-sm" id="resolutionSelect" required>
                                    @foreach(\App\Enums\DisputeResolution::cases() as $res)
                                        <option value="{{ $res->value }}">{{ $res->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 d-none" id="splitShare">
                                <label class="form-label small fw-semibold">Vendor share (₦)</label>
                                <input type="number" name="vendor_share" step="0.01" min="0" class="form-control form-control-sm"
                                       value="{{ old('vendor_share') }}">
                                <div class="form-text">Released to vendor; remainder refunds to buyer.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Note <span class="text-muted fw-normal">(optional)</span></label>
                                <textarea name="note" rows="2" class="form-control form-control-sm">{{ old('note') }}</textarea>
                            </div>
                            <button class="btn btn-success btn-sm w-100">Apply Resolution</button>
                        </form>
                    </div>
                </div>

                @if($dispute->canBeEscalated())
                    <form action="{{ route('admin.disputes.escalate', $dispute) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm w-100">Escalate for Senior Review</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>

<script>
    document.getElementById('resolutionSelect')?.addEventListener('change', function () {
        document.getElementById('splitShare').classList.toggle('d-none', this.value !== 'split');
    });
</script>
@endsection
