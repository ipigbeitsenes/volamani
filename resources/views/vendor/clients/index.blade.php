@extends('layouts.vendor')

@section('title', 'Clients')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">Clients</h4>
            <p class="text-muted mb-0">Your customer relationships in one place.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('vendor.clients.sync') }}" method="POST">@csrf
                <button class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat me-1"></i>Sync from sales</button>
            </form>
            <a href="{{ route('vendor.clients.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add client</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @php $cards = [['Total', $stats['total'], 'primary'], ['Active', $stats['active'], 'success'], ['Leads', $stats['leads'], 'info'], ['Lifetime value', money($stats['lifetime']), 'dark']]; @endphp
        @foreach($cards as [$label, $value, $color])
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <p class="text-muted small mb-1">{{ $label }}</p>
                    <h4 class="fw-bold mb-0 text-{{ $color }}">{{ $value }}</h4>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Name, email or company">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        @foreach(\App\Enums\ClientStatus::cases() as $status)
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
                    <tr><th>Client</th><th>Company</th><th>Status</th><th>Orders</th><th>Lifetime</th><th>Last activity</th></tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center fw-semibold" style="width:34px;height:34px;font-size:.8rem;">{{ $client->initials() }}</span>
                                    <div>
                                        <a href="{{ route('vendor.clients.show', $client) }}" class="fw-semibold text-decoration-none">{{ $client->name }}</a>
                                        <div class="small text-muted">{{ $client->email ?? $client->phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $client->company ?? '—' }}</td>
                            <td><span class="badge bg-{{ $client->status->badge() }}">{{ $client->status->label() }}</span></td>
                            <td>{{ $client->orders_count }}</td>
                            <td class="fw-semibold">{{ money($client->total_spent) }}</td>
                            <td class="small text-muted">{{ $client->last_interaction_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No clients yet. Add one or sync from your sales.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $clients->withQueryString()->links() }}</div>
</div>
@endsection
