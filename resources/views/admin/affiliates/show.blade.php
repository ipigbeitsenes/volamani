@extends('layouts.admin')

@section('title', 'Affiliate Account')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.affiliates.index') }}">Affiliates</a></li>
    <li class="breadcrumb-item active">{{ $account->user->name ?? 'Account' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $account->user->name ?? '—' }}</h4>
            <p class="text-muted mb-0">{{ $account->user->email ?? '' }} · code <code>{{ $account->code() }}</code></p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-{{ $account->status->badge() }} align-self-center">{{ $account->status->label() }}</span>
            @if($account->status === \App\Enums\AffiliateStatus::Active)
                <form action="{{ route('admin.affiliates.suspend', $account) }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">Suspend</button>
                </form>
            @else
                <form action="{{ route('admin.affiliates.activate', $account) }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-outline-success">Activate</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        @php $cards = [
            ['Clicks', $account->clicks_count],
            ['Signups', $account->signups_count],
            ['Conversions', $account->conversions_count],
            ['Total earned', money($account->total_earned)],
            ['Paid out', money($account->total_paid)],
            ['Pending', money($account->pendingEarnings())],
        ]; @endphp
        @foreach($cards as [$label, $value])
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">{{ $label }}</p>
                        <h5 class="fw-bold mb-0">{{ $value }}</h5>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0">Commissions</h6></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th>Reference</th><th>Type</th><th>Amount</th><th>Status</th><th class="text-end">Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($commissions as $c)
                                <tr>
                                    <td><code>{{ $c->reference }}</code></td>
                                    <td>{{ $c->type->label() }}</td>
                                    <td class="fw-semibold">{{ money($c->amount) }}</td>
                                    <td><span class="badge bg-{{ $c->status->badge() }}">{{ $c->status->label() }}</span></td>
                                    <td class="text-end">
                                        @if($c->canBeApproved())
                                            <form action="{{ route('admin.affiliates.commissions.approve', $c) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-success">Pay</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No commissions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-body">{{ $commissions->links() }}</div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0">Recent referrals</h6></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th>User</th><th>Status</th><th>Joined</th></tr>
                        </thead>
                        <tbody>
                            @forelse($referrals as $r)
                                <tr>
                                    <td>{{ $r->referredUser->name ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $r->status->badge() }}">{{ $r->status->label() }}</span></td>
                                    <td class="small text-muted">{{ $r->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No referrals.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
