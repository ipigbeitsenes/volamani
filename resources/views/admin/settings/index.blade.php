@extends('layouts.admin')

@section('title', 'Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
@php
    $meta = [
        'features'      => ['Features', 'bi-toggles', 'Turn platform features on or off. When off, their menus/pages are hidden and their routes are blocked. Escrow protection keeps running even when its buyer pages are hidden.'],
        'general'       => ['Site &amp; General', 'bi-globe', 'Platform name, contact details, social links and global toggles.'],
        'branding'      => ['Branding', 'bi-palette', 'Your site logo and favicon, shown across the platform.'],
        'storage'       => ['File Storage', 'bi-hdd-stack', 'Where uploaded files (images, documents, product files) are stored.'],
        'finance'       => ['Finance &amp; Fees', 'bi-cash-coin', 'Commissions, withdrawal limits and platform fees.'],
        'marketplace'   => ['Marketplace', 'bi-shop', 'Listing rules and download settings.'],
        'security'      => ['Security', 'bi-shield-lock', 'Login attempts and account lockout.'],
        'affiliate'     => ['Affiliate Program', 'bi-people', 'Referral commissions and payouts.'],
        'subscription'  => ['Subscriptions', 'bi-arrow-repeat', 'Vendor plan billing behaviour.'],
        'matching'      => ['Business Matching', 'bi-diagram-3', 'Lead matching thresholds.'],
        'notifications' => ['Notifications', 'bi-bell', 'Global notification controls.'],
        'protection'    => ['Buyer Protection', 'bi-shield-check', 'Chargeback reserve, dispute SLAs, strike threshold and the public protection-policy page copy.'],
    ];
    // Preferred order, then any other groups that exist.
    $order = collect(array_keys($meta))->filter(fn ($g) => $groups->has($g))
        ->merge($groups->keys()->reject(fn ($g) => array_key_exists($g, $meta)))
        ->values();
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Platform Settings</h4>
        <p class="text-muted small mb-0">Global configuration — changes take effect immediately.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        {{-- Tabs --}}
        <div class="col-lg-3">
            <div class="nav flex-column nav-pills sticky-top" style="top:1rem;" role="tablist">
                @foreach($order as $i => $group)
                    @php $m = $meta[$group] ?? [ucfirst($group), 'bi-gear', '']; @endphp
                    <button class="nav-link text-start {{ $i === 0 ? 'active' : '' }}" data-bs-toggle="pill"
                            data-bs-target="#tab-{{ $group }}" type="button" role="tab">
                        <i class="bi {{ $m[1] }} me-2"></i>{!! $m[0] !!}
                    </button>
                @endforeach
                <button class="btn btn-primary mt-3"><i class="bi bi-check-lg me-1"></i>Save all settings</button>
            </div>
        </div>

        {{-- Panes --}}
        <div class="col-lg-9">
            <div class="tab-content">
                @foreach($order as $i => $group)
                    @php $m = $meta[$group] ?? [ucfirst($group), 'bi-gear', '']; @endphp
                    <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="tab-{{ $group }}" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0"><i class="bi {{ $m[1] }} me-2 text-primary"></i>{!! $m[0] !!}</h6>
                                @if($m[2])<div class="text-muted small mt-1">{!! $m[2] !!}</div>@endif
                            </div>
                            <div class="card-body">
                                @if($group === 'storage')
                                    <div class="alert alert-info small d-flex gap-2">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <div>Switching to <strong>Amazon S3</strong> sends <em>new</em> uploads to your bucket; existing local files are not moved. The bucket must allow public read for images, and the AWS S3 SDK package must be installed on the server.</div>
                                    </div>
                                @endif

                                @foreach($settings = $groups->get($group) as $setting)
                                    @if(in_array($setting->key, ['site_logo', 'site_favicon'], true))
                                        @php
                                            $isLogo  = $setting->key === 'site_logo';
                                            $current = \App\Models\Setting::get($setting->key);
                                            $field   = $isLogo ? 'logo_file' : 'favicon_file';
                                        @endphp
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold mb-1">{{ $setting->label ?? $setting->key }}</label>
                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                <div class="border rounded-3 d-flex align-items-center justify-content-center bg-light flex-shrink-0"
                                                     style="width:{{ $isLogo ? '140px' : '64px' }};height:64px;overflow:hidden;">
                                                    @if($current)
                                                        <img src="{{ media_url($current) }}" alt="current {{ $setting->key }}" style="max-width:100%;max-height:100%;object-fit:contain;">
                                                    @else
                                                        <i class="bi bi-image text-muted fs-4"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1" style="min-width:220px;">
                                                    <input type="file" class="form-control @error($field) is-invalid @enderror"
                                                           name="{{ $field }}" accept="image/*{{ $isLogo ? '' : ',.ico' }}">
                                                    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                    <div class="form-text">
                                                        {{ $isLogo
                                                            ? 'Recommended: transparent PNG/SVG, around 200×48px. Max 5 MB.'
                                                            : 'Recommended: square PNG or .ico, 32×32 or 64×64. Max 2 MB.' }}
                                                    </div>
                                                    @if($current)
                                                        <div class="form-check mt-1">
                                                            <input class="form-check-input" type="checkbox" value="1"
                                                                   name="remove_{{ $isLogo ? 'logo' : 'favicon' }}" id="rm_{{ $setting->key }}">
                                                            <label class="form-check-label small text-danger" for="rm_{{ $setting->key }}">
                                                                Remove current {{ $isLogo ? 'logo' : 'favicon' }}
                                                            </label>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @continue
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold mb-1">{{ $setting->label ?? $setting->key }}</label>

                                        @if($setting->key === 'storage_driver')
                                            <select class="form-select" name="settings[{{ $setting->key }}]">
                                                <option value="local" @selected($setting->value === 'local')>Local Disk (server storage)</option>
                                                <option value="s3" @selected($setting->value === 's3')>Amazon S3 (cloud)</option>
                                            </select>
                                        @elseif($setting->type === 'boolean')
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                <input class="form-check-input" type="checkbox" name="settings[{{ $setting->key }}]" value="1" @checked(\App\Models\Setting::get($setting->key))>
                                            </div>
                                        @elseif($setting->type === 'integer')
                                            <input type="number" class="form-control" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                                        @elseif(\Illuminate\Support\Str::contains($setting->key, 'secret'))
                                            <input type="password" class="form-control" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" autocomplete="off">
                                        @elseif($setting->type === 'json')
                                            <textarea class="form-control font-monospace" rows="3" name="settings[{{ $setting->key }}]">{{ $setting->value }}</textarea>
                                        @elseif($setting->type === 'text')
                                            <textarea class="form-control" rows="4" name="settings[{{ $setting->key }}]">{{ $setting->value }}</textarea>
                                        @else
                                            <input type="text" class="form-control" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                                        @endif

                                        <div class="form-text">{{ $setting->key }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</form>
@endsection
