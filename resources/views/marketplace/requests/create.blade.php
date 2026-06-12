@extends('layouts.app')

@section('title', 'Post a Request — Volamani')

@section('content')
<div class="container py-4" style="max-width: 780px;">
    <div class="mb-4">
        <a href="{{ route('marketplace.requests.index') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i> Browse Requests
        </a>
        <h4 class="fw-bold mb-0 mt-1">Post a Request</h4>
        <p class="text-muted small">Describe what you need and let vendors come to you with their best offers.</p>
    </div>

    <form action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">What do you need?</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Request Title <span class="text-danger">*</span></label>
                    <input type="text" name="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}"
                        placeholder="e.g. Need a logo design for my food delivery startup"
                        required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">Select a category (optional)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Detailed Description <span class="text-danger">*</span></label>
                    <textarea name="description" rows="6"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Be specific about your requirements, preferred style, dimensions, format, etc. The more detail you provide, the better quotations you'll receive."
                        required>{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Location <small class="text-muted">(optional)</small></label>
                    <input type="text" name="location" class="form-control form-control-sm"
                        value="{{ old('location') }}" placeholder="Lagos, Nigeria">
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Budget & Timeline</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Minimum Budget (₦) <small class="text-muted">(optional)</small></label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number" name="budget_min"
                                class="form-control @error('budget_min') is-invalid @enderror"
                                value="{{ old('budget_min') }}" min="0"
                                placeholder="0">
                        </div>
                        @error('budget_min') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Maximum Budget (₦) <small class="text-muted">(optional)</small></label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number" name="budget_max"
                                class="form-control @error('budget_max') is-invalid @enderror"
                                value="{{ old('budget_max') }}" min="0"
                                placeholder="e.g. 50000">
                        </div>
                        @error('budget_max') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Submission Deadline <small class="text-muted">(optional)</small></label>
                    <input type="date" name="deadline_at"
                        class="form-control @error('deadline_at') is-invalid @enderror"
                        value="{{ old('deadline_at') }}"
                        min="{{ now()->addDay()->format('Y-m-d') }}">
                    <div class="form-text">Last date you want to receive quotations.</div>
                    @error('deadline_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Attachments <small class="text-muted fw-normal">(optional, max 5 files)</small></div>
            <div class="card-body">
                <input type="file" name="attachments[]" multiple class="form-control @error('attachments.*') is-invalid @enderror">
                <div class="form-text">Upload reference files, mood boards, examples, etc. (max 20MB each)</div>
                @error('attachments.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Visibility</div>
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_public" id="isPublic"
                        value="1" {{ old('is_public', '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="isPublic">
                        Make this request public (all approved vendors can see and bid)
                    </label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-megaphone me-1"></i> Post Request
            </button>
            <a href="{{ route('marketplace.requests.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
