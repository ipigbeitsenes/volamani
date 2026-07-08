@extends('layouts.admin')

@section('title', 'Buyer — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.buyers.index') }}">Buyer Standing</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- Standing + history --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                    <div class="text-muted small">{{ $user->email }} · joined {{ $user->created_at->format('M Y') }}</div>
                </div>
                <div class="text-end">
                    @if($user->purchases_suspended)
                        <span class="badge bg-danger fs-6"><i class="bi bi-slash-circle me-1"></i>Suspended</span>
                        <div class="text-muted small mt-1">since {{ optional($user->purchases_suspended_at)->diffForHumans() }}</div>
                    @elseif($user->buyer_flagged)
                        <span class="badge bg-warning text-dark fs-6"><i class="bi bi-flag me-1"></i>Flagged</span>
                        <div class="text-muted small mt-1">since {{ optional($user->buyer_flagged_at)->diffForHumans() }}</div>
                    @else
                        <span class="badge bg-success fs-6">Good standing</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Strike history</span>
                <span class="badge bg-danger">{{ $user->buyerStrikes->where('cleared_at', null)->count() }} active</span>
            </div>
            <div class="card-body p-0">
                @if($user->buyerStrikes->isEmpty())
                    <div class="text-center text-muted py-5"><i class="bi bi-check2-circle fs-3 d-block mb-2"></i>No strikes on record.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Reason</th><th>Note</th><th>By</th><th>When</th><th class="text-end">Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach($user->buyerStrikes as $strike)
                                    <tr class="{{ $strike->isActive() ? '' : 'text-muted' }}">
                                        <td>
                                            <span class="badge bg-{{ $strike->isActive() ? 'danger' : 'secondary' }}">{{ $strike->reason->label() }}</span>
                                            <div class="text-muted small text-capitalize">{{ $strike->source }}</div>
                                        </td>
                                        <td class="small">{{ $strike->note ?: '—' }}</td>
                                        <td class="small">{{ $strike->issuedBy->name ?? 'System' }}</td>
                                        <td class="small">{{ $strike->created_at->diffForHumans() }}</td>
                                        <td class="text-end">
                                            @if($strike->isActive())
                                                <form method="POST" action="{{ route('admin.buyers.strikes.clear', $strike) }}" onsubmit="return confirm('Clear this strike?')">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success">Clear</button>
                                                </form>
                                            @else
                                                <span class="small">cleared {{ optional($strike->cleared_at)->diffForHumans() }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="col-lg-4">
        @if($user->purchases_suspended || $user->buyer_flagged)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold">Reinstate buyer</h6>
                    <p class="text-muted small">Clears all active strikes and lifts every restriction.</p>
                    <form method="POST" action="{{ route('admin.buyers.reinstate', $user) }}" onsubmit="return confirm('Clear all active strikes and reinstate this buyer?')">
                        @csrf
                        <button class="btn btn-success w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Reinstate</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold">Add a strike</h6>
                <form method="POST" action="{{ route('admin.buyers.strikes.store', $user) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Reason</label>
                        <select name="reason" class="form-select form-select-sm" required>
                            @foreach(\App\Enums\BuyerStrikeReason::cases() as $r)
                                <option value="{{ $r->value }}">{{ $r->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Note (optional)</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2" maxlength="500"></textarea>
                    </div>
                    <button class="btn btn-warning w-100"><i class="bi bi-exclamation-triangle me-1"></i>Record strike</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
