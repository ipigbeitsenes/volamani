@extends('layouts.admin')

@section('title', 'Escrow ' . $escrow->reference)

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('admin.escrows.index') }}" class="text-decoration-none small">&larr; Back to escrow management</a>

    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }} mt-3">{{ session($key) }}</div>@endif
    @endforeach

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 font-monospace">{{ $escrow->reference }}</h5>
                            <p class="text-muted mb-0 small">
                                {{ $escrow->buyer->name ?? '—' }} &rarr; {{ $escrow->vendor->business_name ?? '—' }}
                                · {{ class_basename($escrow->escrowable_type) }} {{ $escrow->escrowable?->reference }}
                            </p>
                        </div>
                        <span class="badge bg-{{ $escrow->status->badge() }}-subtle text-{{ $escrow->status->badge() }} fs-6">
                            {{ $escrow->status->label() }}
                        </span>
                    </div>

                    <div class="row text-center border-top pt-3">
                        <div class="col">
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold">{{ money($escrow->total_amount) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Fee</div>
                            <div class="fw-bold text-muted">{{ money($escrow->platform_fee) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Earnings</div>
                            <div class="fw-bold">{{ money($escrow->vendor_earnings) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Held</div>
                            <div class="fw-bold text-warning">{{ money($escrow->heldAmount()) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Released</div>
                            <div class="fw-bold text-success">{{ money($escrow->released_amount) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Refunded</div>
                            <div class="fw-bold">{{ money($escrow->refunded_amount) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Escrow Activity</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse($escrow->transactions as $tx)
                                <tr>
                                    <td class="text-muted small" style="width:160px">{{ $tx->created_at->format('d M Y, g:ia') }}</td>
                                    <td><span class="badge bg-{{ $tx->type->badge() }}-subtle text-{{ $tx->type->badge() }}">{{ $tx->type->label() }}</span></td>
                                    <td class="small">{{ $tx->description }}</td>
                                    <td class="text-muted small">{{ $tx->actor?->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr><td class="text-muted text-center py-3">No activity yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Resolution</h6>

                    @if($escrow->canRelease())
                        <form action="{{ route('admin.escrows.release', $escrow) }}" method="POST" class="mb-3"
                              onsubmit="return confirm('Release {{ money($escrow->heldAmount()) }} to the vendor?');">
                            @csrf
                            <button class="btn btn-success w-100">Release to Vendor</button>
                        </form>
                    @endif

                    @if($escrow->canRefund())
                        <form action="{{ route('admin.escrows.refund', $escrow) }}" method="POST"
                              onsubmit="return confirm('Refund {{ money($escrow->refundableAmount()) }} to the buyer wallet?');">
                            @csrf
                            <label class="form-label small fw-semibold">Refund reason (optional)</label>
                            <textarea name="reason" rows="2" class="form-control mb-2">{{ old('reason') }}</textarea>
                            <button class="btn btn-outline-danger w-100">Refund to Buyer</button>
                        </form>
                    @endif

                    @if(!$escrow->canRelease() && !$escrow->canRefund())
                        <p class="text-muted small mb-0">This escrow is settled — no resolution actions available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
