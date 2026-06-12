@extends('layouts.vendor')

@section('title', $client->exists ? 'Edit Client' : 'Add Client')

@section('content')
<div class="container-fluid py-4" style="max-width: 720px;">
    <h4 class="fw-bold mb-4">{{ $client->exists ? 'Edit ' . $client->name : 'Add client' }}</h4>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $client->exists ? route('vendor.clients.update', $client) : route('vendor.clients.store') }}">
        @csrf
        @if($client->exists) @method('PUT') @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $client->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company</label>
                        <input type="text" name="company" class="form-control" value="{{ old('company', $client->company) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $client->email) }}">
                        <div class="form-text">If it matches a Volamani account, the client is auto-linked.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach(\App\Enums\ClientStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $client->status?->value ?? 'lead') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tags <span class="text-muted small">(comma-separated)</span></label>
                        <input type="text" name="tags" class="form-control" value="{{ old('tags', is_array($client->tags) ? implode(', ', $client->tags) : '') }}" placeholder="vip, retainer">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $client->address) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">About / notes</label>
                        <textarea name="about" class="form-control" rows="3">{{ old('about', $client->about) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">{{ $client->exists ? 'Save changes' : 'Add client' }}</button>
            <a href="{{ $client->exists ? route('vendor.clients.show', $client) : route('vendor.clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
