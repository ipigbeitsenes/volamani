@extends('layouts.vendor')

@section('title', 'Chargebacks')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Chargebacks</li>
@endsection

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-0">Chargebacks</h4>
    <p class="text-muted mb-0 small">When a buyer disputes a card payment with their bank, it appears here. Submit evidence promptly to contest it.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($chargebacks->isEmpty())
            <div class="text-center text-muted py-5"><i class="bi bi-shield-exclamation fs-2 d-block mb-2"></i>No chargebacks. That's a good thing.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Chargeback</th>
                            <th class="small">Amount</th>
                            <th class="small">Recovered</th>
                            <th class="small">Status</th>
                            <th class="small text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chargebacks as $c)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $c->reference }}</div>
                                <div class="small text-muted">{{ $c->created_at->diffForHumans() }}@if($c->reason) · {{ $c->reason }}@endif</div>
                            </td>
                            <td class="small">{{ money($c->amount) }}</td>
                            <td class="small">{{ money($c->clawed_back_amount) }}</td>
                            <td><span class="badge bg-{{ $c->status->badge() }}">{{ $c->status->label() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('vendor.chargebacks.show', $c) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $chargebacks->links() }}</div>
        @endif
    </div>
</div>
@endsection
