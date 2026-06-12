@extends('layouts.app')

@section('title', 'Notification Preferences')

@section('content')
<div class="container py-4" style="max-width: 820px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Notification preferences</h1>
            <p class="text-muted small mb-0">Choose how you hear from Volamani.</p>
        </div>
        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <form method="POST" action="{{ route('notifications.preferences.update') }}">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Category</th>
                            <th class="text-center" style="width: 110px;"><i class="bi bi-bell me-1"></i>In-app</th>
                            <th class="text-center pe-3" style="width: 110px;"><i class="bi bi-envelope me-1"></i>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrix as $key => $row)
                            @php $cat = $row['category']; @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-{{ $cat->color() }}"><i class="bi {{ $cat->icon() }}"></i></span>
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $cat->label() }}
                                                @if($cat->isEssential())
                                                    <span class="badge bg-secondary ms-1" style="font-size:.6rem;">Always on</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $cat->description() }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input type="hidden" name="preferences[{{ $key }}][database]" value="0">
                                        <input class="form-check-input" type="checkbox"
                                               name="preferences[{{ $key }}][database]" value="1"
                                               {{ $row['database'] ? 'checked' : '' }}
                                               {{ $cat->isEssential() ? 'checked disabled' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="form-check form-switch d-inline-block">
                                        <input type="hidden" name="preferences[{{ $key }}][email]" value="0">
                                        <input class="form-check-input" type="checkbox"
                                               name="preferences[{{ $key }}][email]" value="1"
                                               {{ $row['email'] ? 'checked' : '' }}
                                               {{ $cat->isEssential() ? 'checked disabled' : '' }}>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save preferences</button>
        </div>
    </form>
</div>
@endsection
