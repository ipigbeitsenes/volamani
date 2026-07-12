@extends('layouts.vendor')

@section('title', 'Matching Profile')

@section('content')
<div class="container-fluid py-4" style="max-width: 760px;">
    <h4 class="fw-bold mb-1">Matching Profile</h4>
    <p class="text-muted mb-4">This is what the matching engine uses to pair you with buyer briefs.</p>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('vendor.matching.profile.save') }}">
        @csrf @method('PUT')

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Headline</label>
                        <input type="text" name="headline" class="form-control" value="{{ old('headline', $profile->headline ?? '') }}" placeholder="e.g. Full-stack web & branding studio">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3" placeholder="What you do and who you help…">{{ old('bio', $profile->bio ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Categories <span class="text-muted small">(comma-separated)</span></label>
                        <input type="text" name="categories" class="form-control" value="{{ old('categories', isset($profile->categories) ? implode(', ', $profile->categories) : '') }}" placeholder="Web Development, Branding">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Skills <span class="text-muted small">(comma-separated)</span></label>
                        <input type="text" name="skills" class="form-control" value="{{ old('skills', isset($profile->skills) ? implode(', ', $profile->skills) : '') }}" placeholder="laravel, figma, seo">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Min project budget ({{ currency_symbol() }})</label>
                        <input type="number" step="0.01" min="0" name="min_budget" class="form-control" value="{{ old('min_budget', isset($profile->min_budget) ? from_kobo($profile->min_budget) : '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max project budget ({{ currency_symbol() }})</label>
                        <input type="number" step="0.01" min="0" name="max_budget" class="form-control" value="{{ old('max_budget', isset($profile->max_budget) ? from_kobo($profile->max_budget) : '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">On-site locations <span class="text-muted small">(comma-sep)</span></label>
                        <input type="text" name="locations" class="form-control" value="{{ old('locations', isset($profile->locations) ? implode(', ', $profile->locations) : '') }}" placeholder="Cities you serve">
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="serves_remote" value="1" id="serves_remote" @checked(old('serves_remote', $profile->serves_remote ?? true))>
                            <label class="form-check-label" for="serves_remote">I work remotely / online</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_accepting" value="1" id="is_accepting" @checked(old('is_accepting', $profile->is_accepting ?? true))>
                            <label class="form-check-label" for="is_accepting">Accepting new leads</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">Save profile</button>
            <a href="{{ route('vendor.matching.index') }}" class="btn btn-outline-secondary">Back to leads</a>
        </div>
    </form>
</div>
@endsection
