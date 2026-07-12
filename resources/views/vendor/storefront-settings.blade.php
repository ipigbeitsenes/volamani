@extends('layouts.vendor')

@section('title', 'Storefront Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Storefront</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Storefront Settings</h4>
        <p class="text-muted mb-0 small">Customise how your store looks to buyers</p>
    </div>
    <a href="{{ $vendor->storefront_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-eye me-1"></i>Preview Store
    </a>
</div>

{{-- Approval status --}}
@if($vendor->status->value === 'pending')
    <div class="alert alert-warning d-flex gap-2 mb-4">
        <i class="bi bi-hourglass-split fs-5"></i>
        <div>
            <strong>Account Under Review</strong> — Your vendor account is being reviewed. You can update your storefront details while waiting.
        </div>
    </div>
@elseif($vendor->status->value === 'rejected')
    <div class="alert alert-danger d-flex gap-2 mb-4">
        <i class="bi bi-x-circle fs-5"></i>
        <div>
            <strong>Application Rejected</strong> — {{ $vendor->rejection_reason ?? 'Please contact support for details.' }}
            Update your information and re-submit.
        </div>
    </div>
@endif

<form method="POST" action="{{ route('vendor.storefront.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- Left column --}}
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="business_name" value="{{ old('business_name', $vendor->business_name) }}"
                               class="form-control @error('business_name') is-invalid @enderror" required>
                        @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Tagline</label>
                        <input type="text" name="tagline" value="{{ old('tagline', $vendor->tagline) }}"
                               class="form-control" placeholder="Short description of your business" maxlength="160">
                        <div class="form-text">Shown below your business name on your storefront.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium small">About Your Business</label>
                        <textarea name="description" rows="5" class="form-control"
                                  placeholder="Describe your business, expertise, and what makes you unique...">{{ old('description', $vendor->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Store Focus <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            @foreach(\App\Enums\StoreFocus::cases() as $focus)
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="store_focus" id="focus_{{ $focus->value }}"
                                       value="{{ $focus->value }}" {{ old('store_focus', $vendor->store_focus?->value ?? 'digital') === $focus->value ? 'checked' : '' }} required>
                                <label class="btn btn-outline-primary w-100 text-start py-2" for="focus_{{ $focus->value }}">
                                    <i class="bi {{ $focus->icon() }} d-block mb-1"></i>
                                    <span class="small">{{ $focus->label() }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <div class="form-text">Determines which catalog tools you see. You can change this anytime.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium small">Store Type <span class="text-danger">*</span></label>
                            <select name="store_type" class="form-select" required>
                                @foreach(\App\Enums\StoreType::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('store_type', $vendor->store_type?->value ?? 'individual') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium small">Category</label>
                            <select name="category" class="form-select">
                                <option value="">Select category</option>
                                @foreach(['Web Development','Graphic Design','Digital Marketing','AI & Automation','Branding','Video Production','Photography','Writing & Content','Social Media','Business Consulting','Mobile Apps','Software','UI/UX Design','E-commerce','Other'] as $cat)
                                    <option value="{{ $cat }}" {{ old('category', $vendor->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium small">WhatsApp Business Number</label>
                            <input type="tel" name="whatsapp" value="{{ old('whatsapp', $vendor->whatsapp) }}"
                                   class="form-control" placeholder="+234 800 000 0000">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">Location & Contact</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium small">Website</label>
                            <input type="url" name="website" value="{{ old('website', $vendor->website) }}"
                                   class="form-control @error('website') is-invalid @enderror"
                                   placeholder="https://yoursite.com">
                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium small">Business Address</label>
                            <input type="text" name="address" value="{{ old('address', $vendor->address) }}"
                                   class="form-control" placeholder="Street address">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium small">City</label>
                            <input type="text" name="city" value="{{ old('city', $vendor->city) }}"
                                   class="form-control" placeholder="City">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium small">State / Region</label>
                            <input type="text" name="state" value="{{ old('state', $vendor->state) }}"
                                   class="form-control" placeholder="State / region">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 id="shipping" class="fw-bold mb-0" style="scroll-margin-top:80px;">Shipping <small class="text-muted fw-normal">(physical products)</small></h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium small">Flat Shipping Fee ({{ currency_symbol() }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ currency_symbol() }}</span>
                                <input type="number" step="0.01" min="0" name="shipping_fee"
                                       class="form-control @error('shipping_fee') is-invalid @enderror"
                                       value="{{ old('shipping_fee', $vendor->shipping_fee ? from_kobo($vendor->shipping_fee) : '') }}"
                                       placeholder="0.00">
                                @error('shipping_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">Charged once per order. Leave 0 for free shipping.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium small">Free Shipping Over ({{ currency_symbol() }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ currency_symbol() }}</span>
                                <input type="number" step="0.01" min="0" name="free_shipping_threshold"
                                       class="form-control @error('free_shipping_threshold') is-invalid @enderror"
                                       value="{{ old('free_shipping_threshold', $vendor->free_shipping_threshold ? from_kobo($vendor->free_shipping_threshold) : '') }}"
                                       placeholder="optional">
                                @error('free_shipping_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">Orders at/above this total ship free.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium small">Ships To</label>
                            <input type="text" name="ships_to" class="form-control"
                                   value="{{ old('ships_to', $vendor->ships_to) }}"
                                   placeholder="e.g. Nationwide, 2–5 business days">
                        </div>

                        {{-- Delivery exclusions --}}
                        @php
                            $vlmBlockedStates = old('no_delivery_states', implode(', ', $vendor->no_delivery_states ?? []));
                            $vlmBlockedCities = old('no_delivery_cities', implode(', ', $vendor->no_delivery_cities ?? []));
                        @endphp
                        <div class="col-12">
                            <label class="form-label fw-medium small">States / regions you <span class="text-danger">don't</span> deliver to <span class="text-muted">(optional)</span></label>
                            <input type="text" name="no_delivery_states" class="form-control"
                                   value="{{ $vlmBlockedStates }}"
                                   placeholder="Comma-separated, e.g. California, Texas">
                            <div class="form-text">Buyers with a shipping address in a listed state or region will be blocked from ordering your physical items.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium small">Cities you don't deliver to <span class="text-muted">(optional)</span></label>
                            <input type="text" name="no_delivery_cities" class="form-control"
                                   value="{{ $vlmBlockedCities }}"
                                   placeholder="Comma-separated, e.g. Austin, Portland">
                            <div class="form-text">Use for specific cities within states you otherwise deliver to.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">Social Media Links</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach([['facebook','bi-facebook text-primary','Facebook'],['twitter','bi-twitter-x','X (Twitter)'],['instagram','bi-instagram text-danger','Instagram'],['linkedin','bi-linkedin text-primary','LinkedIn'],['youtube','bi-youtube text-danger','YouTube']] as $s)
                        <div class="col-md-6">
                            <label class="form-label fw-medium small"><i class="bi {{ $s[1] }} me-1"></i>{{ $s[2] }}</label>
                            <input type="url" name="social_links[{{ $s[0] }}]"
                                   value="{{ old('social_links.' . $s[0], $vendor->social_links[$s[0]] ?? '') }}"
                                   class="form-control" placeholder="https://{{ $s[0] }}.com/yourpage">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        {{-- Right column --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">Store Logo</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $vendor->logo_url }}" class="rounded border mb-3 bg-white p-1"
                         style="height:80px;width:auto;max-width:100%;object-fit:contain" id="logoPreview" alt="Logo">
                    <input type="file" name="logo" id="logoInput" class="form-control form-control-sm"
                           accept="image/*" onchange="previewImage(this, 'logoPreview')">
                    <div class="form-text">Recommended: 200×200px, max 2MB</div>
                    @error('logo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">Store Banner</h6>
                </div>
                <div class="card-body">
                    @if($vendor->banner_url)
                        <img src="{{ $vendor->banner_url }}" class="img-fluid rounded mb-2 w-100"
                             style="height:100px;object-fit:cover" id="bannerPreview" alt="Banner">
                    @else
                        <div class="rounded bg-light d-flex align-items-center justify-content-center mb-2"
                             style="height:100px" id="bannerPreview">
                            <span class="text-muted small">No banner set</span>
                        </div>
                    @endif
                    <input type="file" name="banner" class="form-control form-control-sm"
                           accept="image/*" onchange="previewBanner(this)">
                    <div class="form-text">Recommended: 1200×300px, max 5MB</div>
                    @error('banner')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">Store Stats</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-{{ $vendor->status->badge() }}">{{ $vendor->status->label() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Views</span>
                        <span class="fw-medium">{{ number_format($vendor->views_count) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Verified</span>
                        <span>{{ $vendor->isVerified() ? '✅ Yes' : '❌ No' }}</span>
                    </div>
                    @if($vendor->is_featured)
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Featured</span>
                        <span class="badge bg-warning text-dark">⭐ Featured</span>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary px-5 fw-semibold">
            <i class="bi bi-save me-2"></i>Save Storefront
        </button>
    </div>

</form>
@endsection

@push('scripts')
<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById(previewId).src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
function previewBanner(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const el = document.getElementById('bannerPreview');
            if (el.tagName === 'IMG') { el.src = e.target.result; }
            else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-fluid rounded mb-2 w-100';
                img.style = 'height:100px;object-fit:cover';
                img.id = 'bannerPreview';
                el.replaceWith(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
