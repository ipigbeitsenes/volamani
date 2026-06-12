@extends('layouts.admin')

@section('title', $user->name)

@section('content')
<div class="container-fluid" style="max-width: 900px;">
    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-link text-decoration-none mb-3"><i class="bi bi-arrow-left"></i> Back to users</a>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=96&background=1e1b4b&color=fff" class="rounded-circle mb-3" width="96" height="96" alt="">
                    <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                    <div class="text-muted small">{{ $user->email }}</div>
                    <div class="mt-2">
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}-subtle text-{{ $user->is_active ? 'success' : 'secondary' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="mt-2">
                        @foreach($user->roles as $role)
                            <span class="badge bg-primary-subtle text-primary">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Details</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Username</dt><dd class="col-sm-8">{{ $user->username ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Phone</dt><dd class="col-sm-8">{{ $user->phone ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">KYC status</dt><dd class="col-sm-8">{{ $user->kyc_status?->label() ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Wallet balance</dt><dd class="col-sm-8">{{ $user->wallet ? money($user->wallet->balance) : '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Referral code</dt><dd class="col-sm-8">{{ $user->referral_code }}</dd>
                        <dt class="col-sm-4 text-muted">Joined</dt><dd class="col-sm-8">{{ $user->created_at->format('d M Y, H:i') }}</dd>
                        <dt class="col-sm-4 text-muted">Last login</dt><dd class="col-sm-8">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                        @if($user->vendor)
                            <dt class="col-sm-4 text-muted">Store</dt>
                            <dd class="col-sm-8"><a href="{{ route('admin.vendors.show', $user->vendor) }}">{{ $user->vendor->business_name }}</a></dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm border-danger-subtle">
                <div class="card-header bg-white fw-semibold text-danger">Account actions</div>
                <div class="card-body d-flex flex-wrap gap-2">
                    @if($user->id === auth()->id() || $user->hasRole('admin'))
                        <p class="text-muted small mb-0">This account is protected and cannot be modified here.</p>
                    @else
                        <form method="POST" action="{{ route('admin.users.status', $user) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                            <button class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}">
                                {{ $user->is_active ? 'Deactivate' : 'Reactivate' }} account
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('Delete this user account?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete account</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
