@php
    $packages = $service->packages ?? collect();
    $tiers = ['basic', 'standard', 'premium'];
    $pkgByTier = $packages->keyBy(fn($p) => $p->tier->value);
@endphp

<div class="row g-4">
    {{-- Left: Main Info --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Service Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Service Title <span class="text-danger">*</span></label>
                    <input type="text" name="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $service->title ?? '') }}"
                        placeholder="I will design a professional logo for your business" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Short Description <span class="text-danger">*</span></label>
                    <input type="text" name="short_description"
                        class="form-control @error('short_description') is-invalid @enderror"
                        value="{{ old('short_description', $service->short_description ?? '') }}"
                        maxlength="300"
                        placeholder="Brief overview shown in search results">
                    @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Description <span class="text-danger">*</span></label>
                    <textarea name="description" rows="8"
                        class="form-control @error('description') is-invalid @enderror"
                        required>{{ old('description', $service->description ?? '') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Packages --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Pricing Packages</div>
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-3 pt-3" id="pkgTabs">
                    @foreach(['basic' => 'Basic', 'standard' => 'Standard', 'premium' => 'Premium'] as $tier => $label)
                        <li class="nav-item">
                            <button class="nav-link {{ $tier === 'basic' ? 'active' : '' }} fw-semibold"
                                data-bs-toggle="tab" data-bs-target="#pkg_{{ $tier }}">
                                {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
                <div class="tab-content p-3">
                    @foreach(['basic' => 0, 'standard' => 1, 'premium' => 2] as $tier => $idx)
                        @php $pkg = $pkgByTier[$tier] ?? null; @endphp
                        <div class="tab-pane fade {{ $tier === 'basic' ? 'show active' : '' }}" id="pkg_{{ $tier }}">
                            <input type="hidden" name="packages[{{ $idx }}][tier]" value="{{ $tier }}">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Package Name <span class="text-danger">*</span></label>
                                    <input type="text" name="packages[{{ $idx }}][name]"
                                        class="form-control @error('packages.'.$idx.'.name') is-invalid @enderror"
                                        value="{{ old('packages.'.$idx.'.name', $pkg->name ?? '') }}"
                                        placeholder="{{ ucfirst($tier) }} Package">
                                    @error('packages.'.$idx.'.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Price (₦) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₦</span>
                                        <input type="number" name="packages[{{ $idx }}][price]" step="0.01" min="500"
                                            class="form-control @error('packages.'.$idx.'.price') is-invalid @enderror"
                                            value="{{ old('packages.'.$idx.'.price', isset($pkg) ? from_kobo($pkg->price) : '') }}">
                                    </div>
                                    @error('packages.'.$idx.'.price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Package Description <span class="text-danger">*</span></label>
                                <textarea name="packages[{{ $idx }}][description]" rows="2"
                                    class="form-control @error('packages.'.$idx.'.description') is-invalid @enderror">{{ old('packages.'.$idx.'.description', $pkg->description ?? '') }}</textarea>
                                @error('packages.'.$idx.'.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Delivery Days <span class="text-danger">*</span></label>
                                    <input type="number" name="packages[{{ $idx }}][delivery_days]" min="1" max="365"
                                        class="form-control"
                                        value="{{ old('packages.'.$idx.'.delivery_days', $pkg->delivery_days ?? 3) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Revisions</label>
                                    <input type="number" name="packages[{{ $idx }}][revisions]" min="0" max="255"
                                        class="form-control"
                                        value="{{ old('packages.'.$idx.'.revisions', $pkg->revisions ?? 1) }}">
                                    <div class="form-text">Enter 255 for unlimited</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Features Included <small class="text-muted">(one per line)</small></label>
                                <textarea name="packages[{{ $idx }}][features]" rows="4" class="form-control"
                                    placeholder="Source files included&#10;Commercial license&#10;High resolution files">{{ old('packages.'.$idx.'.features', isset($pkg->features) ? implode("\n", $pkg->features) : '') }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- FAQs --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                FAQs
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addFaqRow()">
                    <i class="bi bi-plus-lg me-1"></i> Add FAQ
                </button>
            </div>
            <div class="card-body" id="faqContainer">
                @foreach(old('faqs', $service->faqs ?? []) as $faqIdx => $faq)
                    <div class="faq-row border rounded p-3 mb-2">
                        <div class="mb-2">
                            <input type="text" name="faqs[{{ $faqIdx }}][question]"
                                class="form-control form-control-sm"
                                value="{{ is_array($faq) ? ($faq['question'] ?? '') : $faq->question }}"
                                placeholder="What is included in this service?">
                        </div>
                        <div class="d-flex gap-2">
                            <textarea name="faqs[{{ $faqIdx }}][answer]" rows="2"
                                class="form-control form-control-sm"
                                placeholder="Answer...">{{ is_array($faq) ? ($faq['answer'] ?? '') : $faq->answer }}</textarea>
                            <button type="button" class="btn btn-outline-danger btn-sm align-self-start"
                                onclick="this.closest('.faq-row').remove()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Options --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Organization</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $service->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Cover Image</div>
            <div class="card-body">
                @if(!empty($service->thumbnail ?? null))
                    <div class="mb-2">
                        <img src="{{ $service->thumbnail_url }}" class="img-thumbnail w-100" style="max-height:140px;object-fit:cover;">
                    </div>
                @endif
                <input type="file" name="thumbnail" accept="image/*"
                    class="form-control @error('thumbnail') is-invalid @enderror">
                @error('thumbnail') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">SEO <small class="text-muted fw-normal">(optional)</small></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">SEO Title</label>
                    <input type="text" name="seo_title" class="form-control form-control-sm"
                        value="{{ old('seo_title', $service->seo_title ?? '') }}">
                </div>
                <div>
                    <label class="form-label">SEO Description</label>
                    <textarea name="seo_description" rows="3" class="form-control form-control-sm">{{ old('seo_description', $service->seo_description ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let faqCount = {{ count(old('faqs', $service->faqs ?? [])) }};

function addFaqRow() {
    const container = document.getElementById('faqContainer');
    const div = document.createElement('div');
    div.className = 'faq-row border rounded p-3 mb-2';
    div.innerHTML = `
        <div class="mb-2">
            <input type="text" name="faqs[${faqCount}][question]"
                class="form-control form-control-sm" placeholder="Frequently asked question...">
        </div>
        <div class="d-flex gap-2">
            <textarea name="faqs[${faqCount}][answer]" rows="2"
                class="form-control form-control-sm" placeholder="Answer..."></textarea>
            <button type="button" class="btn btn-outline-danger btn-sm align-self-start"
                onclick="this.closest('.faq-row').remove()">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    container.appendChild(div);
    faqCount++;
}
</script>
@endpush
