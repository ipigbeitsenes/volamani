@extends('layouts.vendor')

@section('title', 'Affiliates')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-1">Affiliate Program</h4>
    <p class="text-muted mb-4">Share your link, refer new users, and earn commission on what they spend.</p>

    @if(! $account)
        {{-- Not enrolled --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-share display-5 text-primary"></i>
                <h5 class="fw-bold mt-3">Earn by referring others to Volamani</h5>
                <p class="text-muted mx-auto" style="max-width: 480px;">
                    Join the affiliate program to get a personal share link. You'll earn
                    <strong>{{ settings('affiliate_commission', 5) }}%</strong> commission whenever someone
                    you refer makes their first purchase.
                </p>
                <form action="{{ route('vendor.affiliates.enroll') }}" method="POST" class="mt-3">
                    @csrf
                    <button class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1"></i>Join the program</button>
                </form>
            </div>
        </div>
    @else
        {{-- Share link --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0">Your share link</h6>
                    <span class="badge bg-{{ $account->status->badge() }}">{{ $account->status->label() }}</span>
                </div>
                <div class="input-group">
                    <input type="text" id="shareLink" class="form-control" value="{{ $account->shareUrl() }}" readonly>
                    <button class="btn btn-outline-primary" type="button" onclick="copyShareLink()">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <div class="small text-muted mt-2">
                    Referral code: <code>{{ $account->code() }}</code> · Commission rate: {{ rtrim(rtrim(number_format($account->effectiveRate(), 2), '0'), '.') }}%
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            @php $stats = [
                ['Clicks', $account->clicks_count, 'bi-cursor', 'primary'],
                ['Signups', $account->signups_count, 'bi-person-plus', 'info'],
                ['Conversions', $account->conversions_count, 'bi-cart-check', 'success'],
                ['Conversion rate', $account->conversionRate() . '%', 'bi-graph-up-arrow', 'secondary'],
            ]; @endphp
            @foreach($stats as [$label, $value, $icon, $color])
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 text-{{ $color }} mb-1">
                                <i class="bi {{ $icon }}"></i>
                                <span class="small text-muted">{{ $label }}</span>
                            </div>
                            <h4 class="fw-bold mb-0">{{ $value }}</h4>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total earned</p>
                        <h4 class="fw-bold mb-0">{{ money($account->total_earned) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Paid to wallet</p>
                        <h4 class="fw-bold mb-0 text-success">{{ money($account->total_paid) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Pending payout</p>
                        <h4 class="fw-bold mb-0 text-warning">{{ money($account->pendingEarnings()) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent commissions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Recent commissions</h6>
                <a href="{{ route('vendor.affiliates.commissions') }}" class="small">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Reference</th><th>Type</th><th>From</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentCommissions as $c)
                            <tr>
                                <td><code>{{ $c->reference }}</code></td>
                                <td><i class="bi {{ $c->type->icon() }} me-1"></i>{{ $c->type->label() }}</td>
                                <td>{{ $c->buyer->name ?? '—' }}</td>
                                <td class="fw-semibold">{{ money($c->amount) }}</td>
                                <td><span class="badge bg-{{ $c->status->badge() }}">{{ $c->status->label() }}</span></td>
                                <td class="small text-muted">{{ $c->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No commissions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent referrals --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Recent referrals</h6>
                <a href="{{ route('vendor.affiliates.referrals') }}" class="small">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>User</th><th>Status</th><th>Joined</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentReferrals as $r)
                            <tr>
                                <td>{{ $r->referredUser->name ?? '—' }}</td>
                                <td><span class="badge bg-{{ $r->status->badge() }}">{{ $r->status->label() }}</span></td>
                                <td class="small text-muted">{{ $r->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No referrals yet — share your link to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function copyShareLink() {
        const input = document.getElementById('shareLink');
        input.select();
        navigator.clipboard.writeText(input.value);
    }
</script>
@endpush
