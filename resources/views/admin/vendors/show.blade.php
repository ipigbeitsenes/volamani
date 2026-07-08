@extends('layouts.admin')

@section('title', $vendor->business_name)

@section('content')
<div class="container-fluid" style="max-width: 900px;">
    <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-link text-decoration-none mb-3"><i class="bi bi-arrow-left"></i> Back to vendors</a>

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <img src="{{ $vendor->logo_url }}" class="rounded bg-white border" width="64" height="64" style="object-fit:contain;padding:2px" alt="">
            <div class="flex-grow-1">
                <h5 class="fw-bold mb-0">{{ $vendor->business_name }}</h5>
                <div class="text-muted small">{{ $vendor->tagline }}</div>
            </div>
            <span class="badge bg-{{ $vendor->status->badge() }}-subtle text-{{ $vendor->status->badge() }} fs-6">{{ $vendor->status->label() }}</span>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Business details</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Owner</dt><dd class="col-sm-8">{{ $vendor->user->name ?? '—' }} ({{ $vendor->user->email ?? '' }})</dd>
                        <dt class="col-sm-4 text-muted">Category</dt><dd class="col-sm-8">{{ $vendor->category ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Location</dt><dd class="col-sm-8">{{ $vendor->city }} {{ $vendor->state }}</dd>
                        <dt class="col-sm-4 text-muted">Products / Services</dt><dd class="col-sm-8">{{ $vendor->products_count }} / {{ $vendor->services_count }}</dd>
                        <dt class="col-sm-4 text-muted">Commission</dt><dd class="col-sm-8">{{ $vendor->getEffectiveCommissionRate() }}%</dd>
                        <dt class="col-sm-4 text-muted">Description</dt><dd class="col-sm-8">{{ $vendor->description ?? '—' }}</dd>
                        @if($vendor->rejection_reason)
                            <dt class="col-sm-4 text-danger">Last note</dt><dd class="col-sm-8 text-danger">{{ $vendor->rejection_reason }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Decision</div>
                <div class="card-body d-grid gap-2">
                    @if($vendor->status !== \App\Enums\Status::Active)
                        <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}">
                            @csrf
                            <button class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i>Approve store</button>
                        </form>
                    @endif

                    <button class="btn btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#rejectBox">Decline application</button>
                    <div class="collapse" id="rejectBox">
                        <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}" class="mt-2">
                            @csrf
                            <textarea name="reason" class="form-control form-control-sm mb-2" rows="2" placeholder="Reason for declining" required></textarea>
                            <button class="btn btn-sm btn-warning w-100">Confirm decline</button>
                        </form>
                    </div>

                    @if($vendor->status === \App\Enums\Status::Active)
                        <button class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#suspendBox">Suspend store</button>
                        <div class="collapse" id="suspendBox">
                            <form method="POST" action="{{ route('admin.vendors.suspend', $vendor) }}" class="mt-2">
                                @csrf
                                <textarea name="reason" class="form-control form-control-sm mb-2" rows="2" placeholder="Reason for suspension" required></textarea>
                                <button class="btn btn-sm btn-danger w-100">Confirm suspension</button>
                            </form>
                        </div>
                    @endif

                    <a href="{{ $vendor->storefront_url }}" target="_blank" class="btn btn-sm btn-outline-secondary">View storefront</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Trust & strikes --}}
    @php($tier = $vendor->trustTier())
    @php($vendorStrikes = $vendor->strikes()->with(['issuedBy', 'clearedBy'])->latest()->get())
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
            <span>Trust &amp; strikes</span>
            <span class="badge bg-{{ $tier->badge() }}"><i class="bi {{ $tier->icon() }} me-1"></i>{{ $tier->label() }}</span>
        </div>
        <div class="card-body">
            <div class="row small mb-3">
                <div class="col-sm-4"><span class="text-muted">Trust score:</span> {{ $vendor->trust_score }}/100</div>
                <div class="col-sm-4"><span class="text-muted">Active strikes:</span> <span class="fw-semibold {{ $vendor->strikes > 0 ? 'text-danger' : '' }}">{{ $vendor->strikes }}</span></div>
                <div class="col-sm-4"><span class="text-muted">Daily withdrawal cap:</span> {{ $tier->withdrawalCapDaily() === null ? 'Unlimited' : money($tier->withdrawalCapDaily()) }}</div>
            </div>

            @if($vendor->suspended_for_strikes)
                <div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-octagon me-1"></i>This store was auto-suspended for reaching the strike threshold. Approve the store above to reinstate.</div>
            @endif

            @if($vendorStrikes->isNotEmpty())
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th class="small">Reason</th><th class="small">Note</th><th class="small">When</th><th class="small text-end">Status</th></tr></thead>
                        <tbody>
                        @foreach($vendorStrikes as $strike)
                            <tr class="{{ $strike->isActive() ? '' : 'text-muted' }}">
                                <td class="small">{{ $strike->reason->label() }}</td>
                                <td class="small">{{ $strike->note ?? '—' }}</td>
                                <td class="small">{{ $strike->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    @if($strike->isActive())
                                        <form method="POST" action="{{ route('admin.vendors.strikes.clear', $strike) }}" class="d-inline">
                                            @csrf<button class="btn btn-sm btn-outline-secondary py-0">Clear</button>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary">Cleared</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.vendors.strikes.store', $vendor) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Add a strike</label>
                    <select name="reason" class="form-select form-select-sm" required>
                        @foreach(\App\Enums\StrikeReason::cases() as $r)
                            <option value="{{ $r->value }}">{{ $r->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Note (optional)</label>
                    <input name="note" class="form-control form-control-sm" maxlength="500" placeholder="Context for this strike">
                </div>
                <div class="col-md-3"><button class="btn btn-sm btn-outline-danger w-100">Record strike</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
