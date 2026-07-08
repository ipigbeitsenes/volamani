@extends('layouts.account')

@section('title', 'New Matching Brief')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <h4 class="fw-bold mb-1">Tell us what you need</h4>
    <p class="text-muted mb-4">We'll instantly match you with vendors that fit your brief.</p>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('matching.store') }}">
        @csrf
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">I'm looking for</label>
                        <select name="looking_for" class="form-select" required>
                            @foreach(\App\Enums\MatchTargetType::cases() as $t)
                                <option value="{{ $t->value }}" @selected(old('looking_for') === $t->value)>{{ $t->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category <span class="text-muted small">(optional)</span></label>
                        <input type="text" name="category" class="form-control" value="{{ old('category') }}" placeholder="e.g. Web Development">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Short summary of what you need" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe your project, goals and any specifics…" required>{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Skills / keywords <span class="text-muted small">(comma-separated)</span></label>
                        <input type="text" name="skills" class="form-control" value="{{ old('skills') }}" placeholder="laravel, branding, seo">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Budget min (₦)</label>
                        <input type="number" step="0.01" min="0" name="budget_min" class="form-control" value="{{ old('budget_min') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Budget max (₦)</label>
                        <input type="number" step="0.01" min="0" name="budget_max" class="form-control" value="{{ old('budget_max') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Timeline</label>
                        <input type="text" name="timeline" class="form-control" value="{{ old('timeline') }}" placeholder="e.g. 2 weeks">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Preferred location <span class="text-muted small">(optional)</span></label>
                        <input type="text" name="preferred_location" class="form-control" value="{{ old('preferred_location') }}" placeholder="e.g. Lagos">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="remote_ok" value="1" id="remote_ok" @checked(old('remote_ok', true))>
                            <label class="form-check-label" for="remote_ok">Open to remote / online vendors</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">Find matches</button>
            <a href="{{ route('matching.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
