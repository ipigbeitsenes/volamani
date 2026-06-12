@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="container-fluid" style="max-width: 760px;">
    <h4 class="fw-bold mb-1">Platform Settings</h4>
    <p class="text-muted small mb-4">Global configuration. Changes take effect immediately.</p>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')

        @foreach($groups as $group => $settings)
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold text-capitalize">{{ $group ?: 'General' }}</div>
                <div class="card-body">
                    @foreach($settings as $setting)
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-1">{{ $setting->label ?? $setting->key }}</label>
                            <div class="small text-muted mb-1">{{ $setting->key }}</div>

                            @if($setting->type === 'boolean')
                                <div class="form-check form-switch">
                                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                    <input class="form-check-input" type="checkbox" name="settings[{{ $setting->key }}]" value="1"
                                           @checked(\App\Models\Setting::get($setting->key))>
                                </div>
                            @elseif($setting->type === 'integer')
                                <input type="number" class="form-control" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                            @elseif($setting->type === 'json')
                                <textarea class="form-control font-monospace" rows="3" name="settings[{{ $setting->key }}]">{{ $setting->value }}</textarea>
                            @else
                                <input type="text" class="form-control" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <button class="btn btn-primary mb-4"><i class="bi bi-check-lg me-1"></i>Save settings</button>
    </form>
</div>
@endsection
