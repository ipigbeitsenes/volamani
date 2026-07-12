@extends('layouts.admin')

@section('title', $plan->exists ? 'Edit Plan' : 'New Plan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.subscriptions.plans') }}">Plans</a></li>
    <li class="breadcrumb-item active">{{ $plan->exists ? 'Edit' : 'New' }}</li>
@endsection

@section('content')
<div class="container-fluid" style="max-width: 820px;">
    <h4 class="fw-bold mb-4">{{ $plan->exists ? 'Edit plan: ' . $plan->name : 'New plan' }}</h4>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $plan->exists ? route('admin.subscriptions.plans.update', $plan) : route('admin.subscriptions.plans.store') }}">
        @csrf
        @if($plan->exists) @method('PUT') @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Billing interval</label>
                        <select name="billing_interval" class="form-select">
                            @foreach(\App\Enums\BillingInterval::cases() as $interval)
                                <option value="{{ $interval->value }}" @selected(old('billing_interval', $plan->billing_interval?->value) === $interval->value)>{{ $interval->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $plan->tagline) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $plan->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Price ({{ currency_symbol() }})</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control"
                               value="{{ old('price', $plan->exists ? from_kobo($plan->price) : 0) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Commission rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="commission_rate" class="form-control"
                               value="{{ old('commission_rate', $plan->commission_rate) }}" placeholder="Platform default">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Trial days</label>
                        <input type="number" min="0" max="365" name="trial_days" class="form-control" value="{{ old('trial_days', $plan->trial_days ?? 0) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max products</label>
                        <input type="number" min="0" name="max_products" class="form-control"
                               value="{{ old('max_products', $plan->max_products) }}" placeholder="Unlimited">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max services</label>
                        <input type="number" min="0" name="max_services" class="form-control"
                               value="{{ old('max_services', $plan->max_services) }}" placeholder="Unlimited">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sort order</label>
                        <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $plan->sort_order ?? 0) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <label class="form-label">Perks <span class="text-muted small">(one per line)</span></label>
                <textarea name="perks" class="form-control" rows="5">{{ old('perks', is_array($plan->perks) ? implode("\n", $plan->perks) : '') }}</textarea>

                <div class="d-flex flex-wrap gap-4 mt-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="featured_listing" value="1" id="featured_listing" @checked(old('featured_listing', $plan->featured_listing))>
                        <label class="form-check-label" for="featured_listing">Grants featured placement</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_popular" value="1" id="is_popular" @checked(old('is_popular', $plan->is_popular))>
                        <label class="form-check-label" for="is_popular">Mark as popular</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active" @checked(old('is_active', $plan->exists ? $plan->is_active : true))>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">{{ $plan->exists ? 'Save changes' : 'Create plan' }}</button>
            <a href="{{ route('admin.subscriptions.plans') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
