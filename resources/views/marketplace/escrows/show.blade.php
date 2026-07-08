@extends('layouts.account')

@section('title', 'Escrow ' . $escrow->reference)

@section('content')
<div class="container py-4">
    <a href="{{ route('escrows.index') }}" class="text-decoration-none small">&larr; Back to escrow</a>

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
                            <p class="text-muted mb-0 small">Seller: {{ $escrow->vendor->business_name ?? '—' }}</p>
                        </div>
                        <span class="badge bg-{{ $escrow->status->badge() }}-subtle text-{{ $escrow->status->badge() }} fs-6">
                            {{ $escrow->status->label() }}
                        </span>
                    </div>

                    <div class="row text-center border-top pt-3">
                        <div class="col">
                            <div class="text-muted small">Total Paid</div>
                            <div class="fw-bold">{{ money($escrow->total_amount) }}</div>
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

                    @if($escrow->auto_release_at && $escrow->isHolding())
                        <div class="alert alert-info mt-3 mb-0 small">
                            Funds release to the seller on
                            <strong>{{ $escrow->auto_release_at->format('d M Y') }}</strong>
                            ({{ $escrow->auto_release_at->diffForHumans() }}, 3 business working days).
                            @if($escrow->isProductEscrow() && $escrow->ticketWindowClosesAt())
                                @if($escrow->canRaiseTicket())
                                    You can open a support ticket about this purchase until
                                    <strong>{{ $escrow->ticketWindowClosesAt()->format('d M Y, g:ia') }}</strong>
                                    ({{ $escrow->ticketWindowClosesAt()->diffForHumans() }}).
                                @else
                                    The 24-hour window to open a support ticket has now closed.
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Activity timeline --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Escrow Activity</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse($escrow->transactions as $tx)
                                <tr>
                                    <td class="text-muted small" style="width:160px">{{ $tx->created_at->format('d M Y, g:ia') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tx->type->badge() }}-subtle text-{{ $tx->type->badge() }}">{{ $tx->type->label() }}</span>
                                    </td>
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

        {{-- Actions --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Actions</h6>

                    @if($escrow->canRaiseTicket())
                        <a href="{{ route('disputes.create', $escrow) }}" class="btn btn-outline-danger w-100">
                            Open a Support Ticket
                        </a>
                        <div class="form-text">
                            Have a problem with this purchase? Open a ticket and our support team will
                            review it with the seller. The funds stay held until it's resolved.
                        </div>
                    @elseif($escrow->isProductEscrow() && $escrow->isHolding())
                        <p class="text-muted small mb-0">
                            The 24-hour window to open a support ticket for this purchase has closed.
                            Funds will release to the seller on schedule.
                        </p>
                    @else
                        <p class="text-muted small mb-0">No actions available for this escrow.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
