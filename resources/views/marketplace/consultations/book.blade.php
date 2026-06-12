@extends('layouts.app')

@section('title', 'Book a Consultation — ' . $consultant->display_name)

@section('content')
<div class="container py-4" style="max-width:700px">
    <a href="{{ route('marketplace.consultants.show', $consultant->slug) }}" class="btn btn-link btn-sm mb-3 ps-0">
        <i class="bi bi-arrow-left me-1"></i> Back to profile
    </a>

    <h4 class="mb-1">Book a Consultation</h4>
    <p class="text-muted mb-4">with <strong>{{ $consultant->display_name }}</strong></p>

    <form method="POST" action="{{ route('consultations.book.store', $consultant->slug) }}">
        @csrf

        {{-- Package selection --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Select a Package</h5>
                @foreach ($packages as $package)
                    <div class="form-check border rounded p-3 mb-2 {{ old('package_id', request('package')) == $package->id ? 'border-primary bg-primary bg-opacity-5' : '' }}">
                        <input class="form-check-input" type="radio" name="package_id"
                            id="pkg_{{ $package->id }}" value="{{ $package->id }}"
                            @checked(old('package_id', request('package')) == $package->id)>
                        <label class="form-check-label d-block" for="pkg_{{ $package->id }}">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold">{{ $package->name }}</span>
                                <strong class="text-success">{{ money($package->price) }}</strong>
                            </div>
                            <div class="text-muted small">{{ $package->durationLabel() }} • {{ $package->type->label() }}</div>
                            <div class="text-muted small mt-1">{{ $package->description }}</div>
                        </label>
                    </div>
                @endforeach
                @error('package_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Schedule --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Preferred Date & Time</h5>

                @if ($consultant->availability->isNotEmpty())
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-clock me-1"></i>
                        <strong>Available:</strong>
                        {{ $consultant->availability->map(fn($a) => $a->day_name . ' ' . $a->time_range)->join(', ') }}
                    </div>
                @endif

                <div class="mb-3">
                    <label for="scheduled_at" class="form-label">Session Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                        value="{{ old('scheduled_at') }}"
                        min="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}"
                        class="form-control @error('scheduled_at') is-invalid @enderror">
                    @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="meeting_platform" class="form-label">Preferred Platform <span class="text-muted">(optional)</span></label>
                    <select name="meeting_platform" id="meeting_platform" class="form-select">
                        <option value="">Consultant's choice</option>
                        <option value="google_meet" @selected(old('meeting_platform') === 'google_meet')>Google Meet</option>
                        <option value="zoom" @selected(old('meeting_platform') === 'zoom')>Zoom</option>
                        <option value="teams" @selected(old('meeting_platform') === 'teams')>Microsoft Teams</option>
                        <option value="phone" @selected(old('meeting_platform') === 'phone')>Phone Call</option>
                        <option value="other" @selected(old('meeting_platform') === 'other')>Other</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Notes / agenda --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Your Agenda</h5>
                <textarea name="notes" id="notes" rows="4"
                    class="form-control @error('notes') is-invalid @enderror"
                    placeholder="Briefly describe what you'd like to discuss…">{{ old('notes') }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Help the consultant prepare. {{ 1000 - strlen(old('notes', '')) }} characters remaining.</div>
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Confirm Booking & Proceed to Payment</button>
        </div>
    </form>
</div>
@endsection
