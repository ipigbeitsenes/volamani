@extends('layouts.vendor')

@section('title', 'Returns')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Returns</li>
@endsection

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Returns &amp; Refunds</h4>
    <p class="text-muted mb-0 small">Review return requests, approve and confirm receipt to refund buyers.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($returns->isEmpty())
            <div class="text-center text-muted py-5"><i class="bi bi-arrow-return-left fs-2 d-block mb-2"></i>No return requests yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Return</th>
                            <th class="small">Order</th>
                            <th class="small">Reason</th>
                            <th class="small">Status</th>
                            <th class="small text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $r)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $r->reference }}</div>
                                <div class="small text-muted">{{ $r->buyer->name ?? '—' }} · {{ $r->created_at->diffForHumans() }}</div>
                                @if($r->description)<div class="small text-muted">{{ Str::limit($r->description, 80) }}</div>@endif
                                @foreach($r->photoUrls() as $url)
                                    <a href="{{ $url }}" target="_blank" class="badge bg-light text-dark border"><i class="bi bi-image"></i> photo</a>
                                @endforeach
                            </td>
                            <td class="small">{{ $r->order->reference ?? '—' }}</td>
                            <td class="small">{{ $r->reason->label() }}</td>
                            <td>
                                <span class="badge bg-{{ $r->status->badge() }}">{{ $r->status->label() }}</span>
                                @if($r->return_tracking)<div class="small text-muted mt-1">Tracking: {{ $r->return_tracking }}</div>@endif
                            </td>
                            <td class="text-end">
                                @if($r->canApprove())
                                    <form method="POST" action="{{ route('vendor.returns.approve', $r) }}" class="d-inline">
                                        @csrf<button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#rej-{{ $r->id }}"><i class="bi bi-x-lg"></i> Reject</button>
                                @elseif($r->canConfirmReceived())
                                    <form method="POST" action="{{ route('vendor.returns.confirm', $r) }}" class="d-inline"
                                          onsubmit="return confirm('Confirm you received the returned item? This refunds the buyer and restocks inventory.');">
                                        @csrf<button class="btn btn-sm btn-primary"><i class="bi bi-box-arrow-in-down"></i> Confirm receipt &amp; refund</button>
                                    </form>
                                @else
                                    <span class="small text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @if($r->canReject())
                        <tr class="collapse" id="rej-{{ $r->id }}">
                            <td colspan="5" class="bg-light">
                                <form method="POST" action="{{ route('vendor.returns.reject', $r) }}" class="row g-2 align-items-end">
                                    @csrf
                                    <div class="col-md-9">
                                        <label class="form-label small text-muted mb-1">Reason for rejecting <span class="text-danger">*</span></label>
                                        <input name="decision_note" class="form-control form-control-sm" maxlength="500" required placeholder="Explain why this return is declined">
                                    </div>
                                    <div class="col-md-3"><button class="btn btn-sm btn-danger w-100">Confirm rejection</button></div>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $returns->links() }}</div>
        @endif
    </div>
</div>
@endsection
