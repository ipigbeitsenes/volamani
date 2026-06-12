@extends('layouts.admin')

@section('title', 'Withdrawals')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Withdrawal Requests</h4>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-5">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="User name or email…">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(\App\Enums\WithdrawalStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-primary w-100">Filter</button></div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Reference</th><th>User</th><th>Amount</th><th>Net</th><th>Requested</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $w)
                            <tr>
                                <td class="small fw-semibold">{{ $w->reference }}</td>
                                <td class="small">{{ $w->user->name ?? '—' }}</td>
                                <td>{{ money($w->amount) }}</td>
                                <td class="small text-muted">{{ money($w->net_amount) }}</td>
                                <td class="small text-muted">{{ $w->created_at->format('d M Y') }}</td>
                                <td><span class="badge bg-{{ $w->status->badge() }}-subtle text-{{ $w->status->badge() }}">{{ $w->status->label() }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.withdrawals.show', $w) }}" class="btn btn-sm btn-outline-secondary">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">No withdrawals found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $withdrawals->withQueryString()->links() }}</div>
</div>
@endsection
