@extends('layouts.app')

@section('title', 'Become a Vendor')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            {{-- Steps indicator --}}
            <div class="text-center mb-5">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:72px;height:72px">
                    <i class="bi bi-shop text-primary fs-2"></i>
                </div>
                <h2 class="fw-bold">Set Up Your Vendor Account</h2>
                <p class="text-muted">Tell buyers about your business. Your account will be reviewed within 24 hours.</p>
            </div>

            {{-- Benefits --}}
            <div class="row g-3 mb-4">
                @foreach([['bi-check-circle-fill text-success','Free to join — no setup fees'],['bi-check-circle-fill text-success','Reach thousands of buyers'],['bi-check-circle-fill text-success','Escrow-protected payments'],['bi-check-circle-fill text-success','Your own branded storefront']] as $b)
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi {{ $b[0] }}"></i>
                        <span class="small">{{ $b[1] }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="card border-0 shadow">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-4">Business Information</h5>

                    <form method="POST" action="{{ route('vendor.onboarding.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-medium small">Business / Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" value="{{ old('business_name') }}"
                                   class="form-control @error('business_name') is-invalid @enderror"
                                   placeholder="e.g. TechStudio NG" required>
                            @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">This will be your public business name on Volamani.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium small">Tagline <span class="text-muted">(optional)</span></label>
                            <input type="text" name="tagline" value="{{ old('tagline') }}"
                                   class="form-control @error('tagline') is-invalid @enderror"
                                   placeholder="e.g. Premium web design for African businesses"
                                   maxlength="160">
                            @error('tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium small">What do you offer? <span class="text-muted">(optional)</span></label>
                            <textarea name="description" rows="4"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Describe your products, services, and expertise...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Category</label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror">
                                    <option value="">Select a category</option>
                                    @foreach(['Web Development','Graphic Design','Digital Marketing','AI & Automation','Branding','Video Production','Photography','Writing & Content','Social Media','Business Consulting','Mobile Apps','Software','UI/UX Design','E-commerce','Other'] as $cat)
                                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">WhatsApp Number</label>
                                <input type="tel" name="whatsapp" value="{{ old('whatsapp', auth()->user()->whatsapp) }}"
                                       class="form-control @error('whatsapp') is-invalid @enderror"
                                       placeholder="+234 800 000 0000">
                                <div class="form-text">Buyers can contact you directly via WhatsApp.</div>
                                @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">City</label>
                                <input type="text" name="city" value="{{ old('city') }}"
                                       class="form-control" placeholder="Lagos">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium small">State</label>
                                <select name="state" class="form-select">
                                    <option value="">Select state</option>
                                    @foreach(['Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','FCT','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara'] as $state)
                                        <option value="{{ $state }}" {{ old('state') === $state ? 'selected' : '' }}>{{ $state }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                <i class="bi bi-rocket me-2"></i>Submit Vendor Application
                            </button>
                        </div>

                        <p class="text-center text-muted small mt-3 mb-0">
                            By submitting, you agree to our <a href="#" class="text-primary text-decoration-none">Vendor Terms of Service</a>.
                        </p>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
