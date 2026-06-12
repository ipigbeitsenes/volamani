@extends('layouts.vendor')

@section('title', 'Affiliate Commissions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Commissions</h4>
            <p class="text-muted mb-0">Everything you've earned through referrals.</p>
        </div>
        <a href="{{ route('vendor.affiliates.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Reference</th><th>Type</th><th>From</th><th>Rate</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @forelse($commissions as $c)
                        <tr>
                            <td><code>{{ $c->reference }}</code></td>
                            <td><i class="bi {{ $c->type->icon() }} me-1"></i>{{ $c->type->label() }}</td>
                            <td>{{ $c->buyer->name ?? '—' }}</td>
                            <td>{{ $c->rate_applied ? rtrim(rtrim(number_format($c->rate_applied, 2), '0'), '.') . '%' : '—' }}</td>
                            <td class="fw-semibold">{{ money($c->amount) }}</td>
                            <td><span class="badge bg-{{ $c->status->badge() }}">{{ $c->status->label() }}</span></td>
                            <td class="small text-muted">{{ $c->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No commissions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $commissions->links() }}</div>
</div>
@endsection
