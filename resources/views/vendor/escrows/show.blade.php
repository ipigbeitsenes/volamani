@extends('layouts.vendor')

@section('title', 'Escrow ' . $escrow->reference)

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('vendor.escrows.index') }}" class="text-decoration-none small">&larr; Back to escrow</a>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 font-monospace">{{ $escrow->reference }}</h5>
                            <p class="text-muted mb-0 small">Buyer: {{ $escrow->buyer->name ?? '—' }}</p>
                        </div>
                        <span class="badge bg-{{ $escrow->status->badge() }}-subtle text-{{ $escrow->status->badge() }} fs-6">
                            {{ $escrow->status->label() }}
                        </span>
                    </div>

                    <div class="row text-center border-top pt-3">
                        <div class="col">
                            <div class="text-muted small">Order Total</div>
                            <div class="fw-bold">{{ money($escrow->total_amount) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Platform Fee</div>
                            <div class="fw-bold text-muted">{{ money($escrow->platform_fee) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Your Earnings</div>
                            <div class="fw-bold text-success">{{ money($escrow->vendor_earnings) }}</div>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Still Held</div>
                            <div class="fw-bold text-warning">{{ money($escrow->heldAmount()) }}</div>
                        </div>
                    </div>

                    @if($escrow->auto_release_at && $escrow->isHolding())
                        <div class="alert alert-info mt-3 mb-0 small">
                            Auto-releases to you {{ $escrow->auto_release_at->diffForHumans() }} unless the buyer disputes.
                        </div>
                    @endif
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
                <div class="card-body small text-muted">
                    Funds are released to your wallet when the buyer confirms receipt, when the
                    protection window elapses, or after a dispute is resolved in your favour.
                </div>
            </div>

            @if($escrow->canDispute())
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">
                        <a href="{{ route('disputes.create', $escrow) }}" class="btn btn-outline-danger w-100">Raise a Dispute</a>
                        <div class="form-text">Open a dispute if there's a problem with this order.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
