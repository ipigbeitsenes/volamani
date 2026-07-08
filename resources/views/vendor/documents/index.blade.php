@extends($docLayout ?? 'layouts.vendor')

@section('title', $type->label() . 's')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $type->label() }}s</h4>
            <p class="text-muted mb-0">
                {{ ($platformTabs ?? false)
                    ? 'Volamani-issued ' . strtolower($type->label()) . 's sent to platform users.'
                    : 'Create and manage ' . strtolower($type->label()) . 's for your clients.' }}
            </p>
        </div>
        <a href="{{ route($routeBase . '.create', ['type' => $type->value]) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New {{ $type->label() }}
        </a>
    </div>

    @if($platformTabs ?? false)
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><a class="nav-link {{ $type === \App\Enums\DocumentType::Invoice ? 'active' : '' }}" href="{{ route($routeBase . '.index', ['type' => 'invoice']) }}">Invoices</a></li>
            <li class="nav-item"><a class="nav-link {{ $type === \App\Enums\DocumentType::Contract ? 'active' : '' }}" href="{{ route($routeBase . '.index', ['type' => 'contract']) }}">Contracts of Sale</a></li>
        </ul>
    @endif

    @if($stats)
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm stat-card">
                    <div class="card-body"><p class="text-muted small mb-1">Outstanding</p>
                        <h4 class="fw-bold mb-0 text-warning">{{ money($stats['outstanding']) }}</h4></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body"><p class="text-muted small mb-1">Paid (total)</p>
                        <h4 class="fw-bold mb-0 text-success">{{ money($stats['paid_total']) }}</h4></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body"><p class="text-muted small mb-1">Drafts</p>
                        <h4 class="fw-bold mb-0">{{ $stats['draft_count'] }}</h4></div>
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Number, client or title">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        @foreach(\App\Enums\DocumentStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Number</th><th>Client</th><th>Total</th><th>{{ $type->isInvoice() ? 'Balance' : 'Valid until' }}</th><th>Status</th><th>Date</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td><a href="{{ route($routeBase . '.show', $doc) }}" class="fw-semibold text-decoration-none"><code>{{ $doc->number }}</code></a></td>
                            <td>{{ $doc->client_name }}</td>
                            <td>{{ money($doc->total) }}</td>
                            <td>
                                @if($type->isInvoice())
                                    {{ $doc->balanceDue() > 0 ? money($doc->balanceDue()) : '—' }}
                                @else
                                    {{ $doc->valid_until?->format('d M Y') ?? '—' }}
                                @endif
                            </td>
                            <td><span class="badge bg-{{ ($doc->isOverdue() ? \App\Enums\DocumentStatus::Overdue : $doc->status)->badge() }}">{{ $doc->isOverdue() ? 'Overdue' : $doc->status->label() }}</span></td>
                            <td class="small text-muted">{{ $doc->issue_date?->format('d M Y') ?? $doc->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route($routeBase . '.show', $doc) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No {{ strtolower($type->label()) }}s yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $documents->withQueryString()->links() }}</div>
</div>
@endsection
