@extends('layouts.finance')

@section('title', 'Payments')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Payments</h4>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search reference...">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(\App\Enums\PaymentStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected(($filters['status'] ?? '') === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="gateway" class="form-select">
                <option value="">All gateways</option>
                @foreach(\App\Enums\PaymentGateway::cases() as $case)
                    <option value="{{ $case->value }}" @selected(($filters['gateway'] ?? '') === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($payments->isEmpty())
                <p class="text-muted text-center py-5 mb-0">No payments found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>User</th>
                                <th>Gateway</th>
                                <th class="text-end">Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $p)
                                <tr>
                                    <td class="font-monospace small">{{ $p->reference }}</td>
                                    <td class="small">{{ $p->user->name ?? 'Guest' }}</td>
                                    <td class="small"><i class="bi {{ $p->gateway->icon() }} me-1"></i>{{ $p->gateway->label() }}</td>
                                    <td class="text-end">{{ money($p->amount) }}</td>
                                    <td class="small text-muted">{{ $p->created_at->format('d M Y') }}</td>
                                    <td><span class="badge bg-{{ $p->status->badge() }}-subtle text-{{ $p->status->badge() }}">{{ $p->status->label() }}</span></td>
                                    <td class="text-end">
                                        @if($p->gateway === \App\Enums\PaymentGateway::BankTransfer && $p->status !== \App\Enums\PaymentStatus::Success)
                                            <form method="POST" action="{{ route('finance.payments.approve-offline', $p) }}" class="d-inline" onsubmit="return confirm('Approve this bank-transfer payment and fulfil the order?');">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                                        @else
                                            <span class="small text-muted">—</span>
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

    <div class="mt-3">{{ $payments->withQueryString()->links() }}</div>
</div>
@endsection
