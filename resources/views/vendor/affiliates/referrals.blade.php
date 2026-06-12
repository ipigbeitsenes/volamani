@extends('layouts.vendor')

@section('title', 'Referrals')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Referrals</h4>
            <p class="text-muted mb-0">People who signed up through your link.</p>
        </div>
        <a href="{{ route('vendor.affiliates.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>User</th><th>Status</th><th>Signup reward</th><th>Joined</th><th>Rewarded</th></tr>
                </thead>
                <tbody>
                    @forelse($referrals as $r)
                        <tr>
                            <td>{{ $r->referredUser->name ?? '—' }}</td>
                            <td><span class="badge bg-{{ $r->status->badge() }}">{{ $r->status->label() }}</span></td>
                            <td>{{ $r->signup_reward > 0 ? money($r->signup_reward) : '—' }}</td>
                            <td class="small text-muted">{{ $r->created_at->format('d M Y') }}</td>
                            <td class="small text-muted">{{ $r->rewarded_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">No referrals yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $referrals->links() }}</div>
</div>
@endsection
