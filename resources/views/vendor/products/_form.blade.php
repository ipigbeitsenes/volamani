<div class="row g-4">
    {{-- Main Details --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Product Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $product->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <input type="text" name="short_description"
                        class="form-control @error('short_description') is-invalid @enderror"
                        value="{{ old('short_description', $product->short_description ?? '') }}"
                        maxlength="500"
                        placeholder="One line summary shown in search results">
                    @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Description <span class="text-danger">*</span></label>
                    <textarea name="description" rows="8" minlength="50"
                        class="form-control @error('description') is-invalid @enderror"
                        required>{{ old('description', $product->description ?? '') }}</textarea>
                    <div class="form-text">Describe your product in detail — at least 50 characters.</div>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Media --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Media</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Thumbnail / Cover Image</label>
                        @if(!empty($product->thumbnail ?? null))
                            <div class="mb-2">
                                <img src="{{ $product->thumbnail_url }}" class="img-thumbnail"
                                     style="max-height: 120px;">
                            </div>
                        @endif
                        <input type="file" name="thumbnail" accept="image/*"
                            class="form-control @error('thumbnail') is-invalid @enderror">
                        @error('thumbnail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Preview / Demo URL</label>
                        <input type="url" name="preview_url"
                            class="form-control @error('preview_url') is-invalid @enderror"
                            value="{{ old('preview_url', $product->preview_url ?? '') }}"
                            placeholder="https://youtube.com/...">
                        @error('preview_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Additional Gallery Images (max 10, 4MB each)</label>
                    <input type="file" name="gallery[]" accept="image/*" multiple
                        class="form-control @error('gallery.*') is-invalid @enderror">
                    @error('gallery.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Downloadable Files --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Product Files</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Upload the files buyers will receive after purchase. Files are stored securely and only accessible to buyers.</p>

                <div id="filesContainer">
                    <div class="file-entry row g-2 mb-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small">File Label</label>
                            <input type="text" name="file_labels[]" class="form-control form-control-sm"
                                placeholder="e.g. Main File, Bonus...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">File (max 100MB)</label>
                            <input type="file" name="files[]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                onclick="this.closest('.file-entry').remove()">Remove</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addFileRow()">
                    <i class="bi bi-plus-lg me-1"></i> Add Another File
                </button>
            </div>
        </div>
    </div>

    {{-- Right Sidebar Options --}}
    <div class="col-lg-4">
        {{-- Pricing --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Pricing</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Price (₦) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₦</span>
                        <input type="number" name="price" step="0.01" min="0"
                            class="form-control @error('price') is-invalid @enderror"
                            value="{{ old('price', isset($product) ? from_kobo($product->price) : '') }}"
                            required>
                    </div>
                    @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Compare-at Price (₦) <small class="text-muted">(original/strikethrough)</small></label>
                    <div class="input-group">
                        <span class="input-group-text">₦</span>
                        <input type="number" name="compare_price" step="0.01" min="0"
                            class="form-control @error('compare_price') is-invalid @enderror"
                            value="{{ old('compare_price', isset($product) && $product->compare_price ? from_kobo($product->compare_price) : '') }}">
                    </div>
                    @error('compare_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Organization --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Organization</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Product Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        @foreach(\App\Enums\ProductType::cases() as $type)
                            <option value="{{ $type->value }}"
                                {{ old('type', $product->type->value ?? '') === $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Download Settings --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Download Settings</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Download Limit
                        <small class="text-muted">(blank = no limit)</small>
                    </label>
                    <input type="number" name="download_limit" min="1"
                        class="form-control @error('download_limit') is-invalid @enderror"
                        value="{{ old('download_limit', $product->download_limit ?? '') }}"
                        placeholder="e.g. 5">
                    @error('download_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Link Expiry Hours</label>
                    <input type="number" name="download_expiry_hours" min="1" max="8760"
                        class="form-control @error('download_expiry_hours') is-invalid @enderror"
                        value="{{ old('download_expiry_hours', $product->download_expiry_hours ?? 48) }}">
                    <div class="form-text">How long download links are valid after generation.</div>
                    @error('download_expiry_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">SEO <small class="text-muted fw-normal">(optional)</small></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">SEO Title</label>
                    <input type="text" name="seo_title"
                        class="form-control form-control-sm @error('seo_title') is-invalid @enderror"
                        value="{{ old('seo_title', $product->seo_title ?? '') }}"
                        maxlength="255">
                    @error('seo_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">SEO Description</label>
                    <textarea name="seo_description" rows="3"
                        class="form-control form-control-sm @error('seo_description') is-invalid @enderror"
                        maxlength="500">{{ old('seo_description', $product->seo_description ?? '') }}</textarea>
                    @error('seo_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addFileRow() {
    const container = document.getElementById('filesContainer');
    const div = document.createElement('div');
    div.className = 'file-entry row g-2 mb-2 align-items-end';
    div.innerHTML = `
        <div class="col-md-4">
            <label class="form-label small">File Label</label>
            <input type="text" name="file_labels[]" class="form-control form-control-sm" placeholder="e.g. Bonus Pack">
        </div>
        <div class="col-md-6">
            <label class="form-label small">File (max 100MB)</label>
            <input type="file" name="files[]" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger btn-sm w-100"
                onclick="this.closest('.file-entry').remove()">Remove</button>
        </div>`;
    container.appendChild(div);
}
</script>
@endpush
