@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Payments</h4>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Reference…">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Any status</option>
                @foreach(\App\Enums\PaymentStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="gateway" class="form-select">
                <option value="">Any gateway</option>
                @foreach(\App\Enums\PaymentGateway::cases() as $g)
                    <option value="{{ $g->value }}" @selected(($filters['gateway'] ?? '') === $g->value)>{{ $g->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Reference</th><th>User</th><th>For</th><th>Gateway</th><th>Amount</th><th>Status</th><th>Date</th><th class="text-end"></th></tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr>
                                <td class="small fw-semibold">{{ $p->reference }}</td>
                                <td class="small">{{ $p->user->name ?? 'Guest' }}</td>
                                <td class="small text-muted">{{ $p->payable_type ? class_basename($p->payable_type) : '—' }}</td>
                                <td class="small">{{ $p->gateway->label() }}</td>
                                <td>{{ money($p->amount) }}</td>
                                <td><span class="badge bg-{{ $p->status->badge() }}-subtle text-{{ $p->status->badge() }}">{{ $p->status->label() }}</span></td>
                                <td class="small text-muted">{{ $p->created_at->format('d M Y') }}</td>
                                <td class="text-end"><a href="{{ route('admin.payments.show', $p) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $payments->withQueryString()->links() }}</div>
</div>
@endsection
