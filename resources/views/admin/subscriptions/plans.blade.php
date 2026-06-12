@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.subscriptions.index') }}">Subscriptions</a></li>
    <li class="breadcrumb-item active">Plans</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Plans</h4>
        <a href="{{ route('admin.subscriptions.plans.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New plan
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>Name</th><th>Price</th><th>Commission</th><th>Limits (P/S)</th><th>Subscribers</th><th>Active</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $plan->name }}</span>
                                @if($plan->is_popular)<span class="badge bg-primary ms-1">Popular</span>@endif
                                <div class="small text-muted">{{ $plan->tagline }}</div>
                            </td>
                            <td>{{ $plan->priceLabel() }}</td>
                            <td>{{ $plan->commission_rate !== null ? rtrim(rtrim(number_format($plan->commission_rate, 2), '0'), '.') . '%' : 'Default' }}</td>
                            <td>{{ $plan->productLimitLabel() }} / {{ $plan->serviceLimitLabel() }}</td>
                            <td>{{ $plan->subscriptions_count }}</td>
                            <td>
                                @if($plan->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.subscriptions.plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('admin.subscriptions.plans.toggle', $plan) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-{{ $plan->is_active ? 'danger' : 'success' }}">
                                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No plans yet. Create your first one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
