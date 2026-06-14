@extends('layouts.vendor')

@section('title', 'Request a Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Category Requests</li>
@endsection

@section('content')

<div class="mb-4">
    <h4 class="fw-bold mb-0">Custom Category Requests</h4>
    <p class="text-muted mb-0 small">Can't find the right category for your listings? Request a new one and our team will review it.</p>
</div>

<div class="row g-4">
    {{-- Request form --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0">New Request</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('vendor.category-requests.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-medium small">Type <span class="text-danger">*</span></label>
                        <select name="domain" id="domainSelect" class="form-select" required>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->value }}" {{ old('domain') === $domain->value ? 'selected' : '' }}>{{ $domain->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small">Proposed Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control"
                               placeholder="e.g. Drone Footage" maxlength="120" required>
                    </div>

                    {{-- Per-domain parent pickers; only the active one is enabled (so only it submits) --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Suggested Parent <span class="text-muted">(optional)</span></label>

                        <select name="parent_id" data-domain="digital" class="form-select domain-parent">
                            <option value="">— Top-level category —</option>
                            @foreach($digitalCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>

                        <select name="parent_id" data-domain="physical" class="form-select domain-parent" disabled>
                            <option value="">— Top-level category —</option>
                            @foreach($physicalCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>

                        <select name="parent_id" data-domain="service" class="form-select domain-parent" disabled>
                            <option value="">— Top-level category —</option>
                            @foreach($serviceCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small">Why do you need it? <span class="text-muted">(optional)</span></label>
                        <textarea name="reason" rows="3" class="form-control" maxlength="500"
                                  placeholder="Help us understand what you'll list here...">{{ old('reason') }}</textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-plus-circle me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Existing requests --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0">Your Requests</h6>
            </div>
            <div class="card-body p-0">
                @if($requests->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-tags fs-2 d-block mb-2"></i>
                        You haven't requested any categories yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Category</th>
                                    <th class="small">Type</th>
                                    <th class="small">Status</th>
                                    <th class="small">Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $req)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $req->name }}</span>
                                        @if($req->admin_note)
                                            <div class="text-muted small">{{ $req->admin_note }}</div>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{ $req->domain->badge() }}">{{ $req->domain->label() }}</span></td>
                                    <td><span class="badge bg-{{ $req->status->badge() }}">{{ $req->status->label() }}</span></td>
                                    <td class="small text-muted">{{ $req->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">{{ $requests->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const domainSelect = document.getElementById('domainSelect');
    function syncParentPickers() {
        const active = domainSelect.value;
        document.querySelectorAll('.domain-parent').forEach(sel => {
            const match = sel.dataset.domain === active;
            sel.classList.toggle('d-none', !match);
            sel.disabled = !match;     // disabled selects are not submitted
        });
    }
    domainSelect.addEventListener('change', syncParentPickers);
    syncParentPickers();
</script>
@endpush
