@extends('layouts.vendor')

@section('title', 'Consultation Packages')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Consultation Packages</h4>
    </div>

    <div class="row g-4">
        {{-- Existing packages --}}
        <div class="col-lg-7">
            <h6 class="text-muted mb-3">Your Packages ({{ $packages->count() }})</h6>
            @forelse ($packages as $package)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-0">{{ $package->name }}</h6>
                                <small class="text-muted">{{ $package->type->label() }} • {{ $package->durationLabel() }}</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <strong class="text-success">{{ money($package->price) }}</strong>
                                <span class="badge bg-{{ $package->is_active ? 'success' : 'secondary' }}">
                                    {{ $package->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </div>
                        </div>
                        <p class="small text-muted mt-2 mb-3">{{ $package->description }}</p>
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('vendor.consultations.packages.toggle', $package) }}">
                                @csrf
                                <button class="btn btn-outline-secondary btn-sm">
                                    {{ $package->is_active ? 'Hide' : 'Activate' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('vendor.consultations.packages.delete', $package) }}"
                                onsubmit="return confirm('Delete this package?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted border rounded">
                    No packages yet. Add one using the form.
                </div>
            @endforelse
        </div>

        {{-- Add package form --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Add Package</h5>
                    <form method="POST" action="{{ route('vendor.consultations.packages.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="form-control form-control-sm @error('name') is-invalid @enderror"
                                placeholder="e.g. Startup Kick-off Call" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select form-select-sm @error('type') is-invalid @enderror" required>
                                <option value="one_time" @selected(old('type') === 'one_time')>One-time Session</option>
                                <option value="retainer" @selected(old('type') === 'retainer')>Monthly Retainer</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col">
                                <label for="duration_minutes" class="form-label">Duration (min) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_minutes" id="duration_minutes"
                                    value="{{ old('duration_minutes', 60) }}"
                                    class="form-control form-control-sm @error('duration_minutes') is-invalid @enderror"
                                    min="15" max="480" required>
                                @error('duration_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col">
                                <label for="price" class="form-label">Price ({{ currency_symbol() }}) <span class="text-danger">*</span></label>
                                <input type="number" name="price" id="price" value="{{ old('price') }}"
                                    class="form-control form-control-sm @error('price') is-invalid @enderror"
                                    min="100" step="100" required>
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3" id="sessions_per_month_field" style="display:none">
                            <label for="max_sessions_per_month" class="form-label">Sessions per Month</label>
                            <input type="number" name="max_sessions_per_month" id="max_sessions_per_month"
                                value="{{ old('max_sessions_per_month', 4) }}"
                                class="form-control form-control-sm @error('max_sessions_per_month') is-invalid @enderror"
                                min="1" max="31">
                            @error('max_sessions_per_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="3"
                                class="form-control form-control-sm @error('description') is-invalid @enderror"
                                required>{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">Add Package</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('type').addEventListener('change', function () {
        document.getElementById('sessions_per_month_field').style.display =
            this.value === 'retainer' ? 'block' : 'none';
    });
    // init on load
    if (document.getElementById('type').value === 'retainer') {
        document.getElementById('sessions_per_month_field').style.display = 'block';
    }
</script>
@endsection
