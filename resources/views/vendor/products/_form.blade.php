@php
    $isEdit       = isset($product) && $product->exists;
    $currentKind  = old('kind', $isEdit ? $product->kind->value : 'digital');
    $detail       = $isEdit ? $product->physicalDetail : null;
    $secondaryIds = $isEdit ? $product->secondaryPhysicalCategories->pluck('id')->all() : (array) old('secondary_categories', []);
    $existingVariants = $isEdit ? $product->variants : collect();
@endphp

{{-- Product Kind --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Product Kind</div>
    <div class="card-body">
        @if($isEdit)
            <input type="hidden" name="kind" value="{{ $currentKind }}">
            <span class="badge bg-{{ $product->kind->badge() }} fs-6">
                <i class="bi {{ $product->kind->icon() }} me-1"></i>{{ $product->kind->label() }}
            </span>
            <div class="form-text">A product's kind can't be changed after creation.</div>
        @else
            <div class="row g-2">
                @foreach(\App\Enums\ProductKind::cases() as $k)
                <div class="col-md-6">
                    <input type="radio" class="btn-check" name="kind" id="kind_{{ $k->value }}" value="{{ $k->value }}"
                           {{ $currentKind === $k->value ? 'checked' : '' }} onchange="syncKind()">
                    <label class="btn btn-outline-primary w-100 text-start py-3" for="kind_{{ $k->value }}">
                        <i class="bi {{ $k->icon() }} fs-4 d-block mb-1"></i>
                        <span class="fw-semibold">{{ $k->label() }}</span>
                        <span class="d-block small text-muted">
                            {{ $k === \App\Enums\ProductKind::Digital ? 'Files delivered instantly after purchase' : 'Shipped to the buyer; tracks stock & variants' }}
                        </span>
                    </label>
                </div>
                @endforeach
            </div>
            @error('kind') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        @endif
    </div>
</div>

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
                                <img src="{{ $product->thumbnail_url }}" class="img-thumbnail" style="max-height: 120px;">
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

        {{-- DIGITAL: Downloadable Files --}}
        <div class="card border-0 shadow-sm mb-4 kind-digital">
            <div class="card-header bg-white fw-semibold">Product Files</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Upload the files buyers will receive after purchase. Files are stored securely and only accessible to buyers.</p>
                <div id="filesContainer">
                    <div class="file-entry row g-2 mb-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small">File Label</label>
                            <input type="text" name="file_labels[]" class="form-control form-control-sm" placeholder="e.g. Main File, Bonus...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">File (max 100MB)</label>
                            <input type="file" name="files[]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.file-entry').remove()">Remove</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addFileRow()">
                    <i class="bi bi-plus-lg me-1"></i> Add Another File
                </button>
            </div>
        </div>

        {{-- PHYSICAL: Inventory --}}
        <div class="card border-0 shadow-sm mb-4 kind-physical">
            <div class="card-header bg-white fw-semibold">Inventory</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" min="0"
                            class="form-control @error('stock_quantity') is-invalid @enderror"
                            value="{{ old('stock_quantity', $detail->stock_quantity ?? 0) }}">
                        <div class="form-text">Ignored if you add variants below.</div>
                        @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label d-block">Options</label>
                        <input type="hidden" name="track_inventory" value="0">
                        <div class="form-check form-check-inline mt-2">
                            <input class="form-check-input" type="checkbox" name="track_inventory" value="1" id="track_inventory"
                                {{ old('track_inventory', $detail->track_inventory ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_inventory">Track inventory</label>
                        </div>
                        <input type="hidden" name="allow_backorder" value="0">
                        <div class="form-check form-check-inline mt-2">
                            <input class="form-check-input" type="checkbox" name="allow_backorder" value="1" id="allow_backorder"
                                {{ old('allow_backorder', $detail->allow_backorder ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_backorder">Allow backorders</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PHYSICAL: Variants --}}
        <div class="card border-0 shadow-sm mb-4 kind-physical">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span>Variants <small class="text-muted fw-normal">(optional — e.g. Size / Colour)</small></span>
            </div>
            <div class="card-body">
                <div id="variantsContainer">
                    @forelse($existingVariants as $v)
                    <div class="variant-entry row g-2 mb-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small">Variant Name</label>
                            <input type="text" name="variant_names[]" class="form-control form-control-sm" value="{{ $v->name }}" placeholder="e.g. Large / Black">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">SKU</label>
                            <input type="text" name="variant_skus[]" class="form-control form-control-sm" value="{{ $v->sku }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Price (₦)</label>
                            <input type="number" step="0.01" min="0" name="variant_prices[]" class="form-control form-control-sm" value="{{ $v->price_override ? from_kobo($v->price_override) : '' }}" placeholder="base">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Stock</label>
                            <input type="number" min="0" name="variant_stocks[]" class="form-control form-control-sm" value="{{ $v->stock_quantity }}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.variant-entry').remove()">&times;</button>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addVariantRow()">
                    <i class="bi bi-plus-lg me-1"></i> Add Variant
                </button>
                <div class="form-text mt-1">Leave the price blank to use the base price. Stock here overrides the quantity above.</div>
            </div>
        </div>
    </div>

    {{-- Right Sidebar --}}
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
                            value="{{ old('price', isset($product) ? from_kobo($product->price) : '') }}" required>
                    </div>
                    @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Compare-at Price (₦) <small class="text-muted">(strikethrough)</small></label>
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

        {{-- DIGITAL: Organization --}}
        <div class="card border-0 shadow-sm mb-4 kind-digital">
            <div class="card-header bg-white fw-semibold">Organization</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Product Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                        @foreach(\App\Enums\ProductType::cases() as $type)
                            <option value="{{ $type->value }}" {{ old('type', $product->type->value ?? 'digital') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- DIGITAL: Download Settings --}}
        <div class="card border-0 shadow-sm mb-4 kind-digital">
            <div class="card-header bg-white fw-semibold">Download Settings</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Download Limit <small class="text-muted">(blank = no limit)</small></label>
                    <input type="number" name="download_limit" min="1"
                        class="form-control @error('download_limit') is-invalid @enderror"
                        value="{{ old('download_limit', $product->download_limit ?? '') }}" placeholder="e.g. 5">
                    @error('download_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Link Expiry Hours</label>
                    <input type="number" name="download_expiry_hours" min="1" max="8760"
                        class="form-control @error('download_expiry_hours') is-invalid @enderror"
                        value="{{ old('download_expiry_hours', $product->download_expiry_hours ?? 48) }}">
                    @error('download_expiry_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- PHYSICAL: Categories & Item Details --}}
        <div class="card border-0 shadow-sm mb-4 kind-physical">
            <div class="card-header bg-white fw-semibold">Category & Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Primary Category <span class="text-danger">*</span></label>
                    <select name="physical_category_id" class="form-select @error('physical_category_id') is-invalid @enderror">
                        <option value="">Select Category</option>
                        @foreach($physicalCategories as $root)
                            <option value="{{ $root->id }}" {{ old('physical_category_id', $product->physical_category_id ?? '') == $root->id ? 'selected' : '' }}>{{ $root->name }}</option>
                            @foreach($root->children as $child)
                                <option value="{{ $child->id }}" {{ old('physical_category_id', $product->physical_category_id ?? '') == $child->id ? 'selected' : '' }}>&nbsp;&nbsp;— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('physical_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Can't find it? <a href="{{ route('vendor.category-requests.index') }}" target="_blank">Request a category</a>.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Secondary Categories <small class="text-muted">(optional, max 5)</small></label>
                    <select name="secondary_categories[]" multiple size="5" class="form-select @error('secondary_categories.*') is-invalid @enderror">
                        @foreach($physicalCategories as $root)
                            <optgroup label="{{ $root->name }}">
                                @foreach($root->children as $child)
                                    <option value="{{ $child->id }}" {{ in_array($child->id, $secondaryIds) ? 'selected' : '' }}>{{ $child->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Condition <span class="text-danger">*</span></label>
                    <select name="condition" class="form-select @error('condition') is-invalid @enderror">
                        @foreach(\App\Enums\ProductCondition::cases() as $cond)
                            <option value="{{ $cond->value }}" {{ old('condition', $detail->condition->value ?? 'new') === $cond->value ? 'selected' : '' }}>{{ $cond->label() }}</option>
                        @endforeach
                    </select>
                    @error('condition') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand', $detail->brand ?? '') }}">
                </div>
            </div>
        </div>

        {{-- PHYSICAL: Shipping dimensions --}}
        <div class="card border-0 shadow-sm mb-4 kind-physical">
            <div class="card-header bg-white fw-semibold">Shipping <small class="text-muted fw-normal">(optional)</small></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Weight (grams)</label>
                    <input type="number" name="weight_grams" min="0" class="form-control" value="{{ old('weight_grams', $detail->weight_grams ?? '') }}">
                </div>
                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label small">Length (mm)</label>
                        <input type="number" name="length_mm" min="0" class="form-control form-control-sm" value="{{ old('length_mm', $detail->length_mm ?? '') }}">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Width (mm)</label>
                        <input type="number" name="width_mm" min="0" class="form-control form-control-sm" value="{{ old('width_mm', $detail->width_mm ?? '') }}">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Height (mm)</label>
                        <input type="number" name="height_mm" min="0" class="form-control form-control-sm" value="{{ old('height_mm', $detail->height_mm ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- SEO (shared) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">SEO <small class="text-muted fw-normal">(optional)</small></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">SEO Title</label>
                    <input type="text" name="seo_title" class="form-control form-control-sm" value="{{ old('seo_title', $product->seo_title ?? '') }}" maxlength="255">
                </div>
                <div class="mb-3">
                    <label class="form-label">SEO Description</label>
                    <textarea name="seo_description" rows="3" class="form-control form-control-sm" maxlength="500">{{ old('seo_description', $product->seo_description ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function syncKind() {
    const kind = document.querySelector('input[name=kind]')?.value
              || document.querySelector('input[name=kind]:checked')?.value
              || 'digital';
    const checked = document.querySelector('input[name=kind]:checked')?.value || kind;
    document.querySelectorAll('.kind-digital').forEach(el => el.classList.toggle('d-none', checked !== 'digital'));
    document.querySelectorAll('.kind-physical').forEach(el => el.classList.toggle('d-none', checked !== 'physical'));
}

function addFileRow() {
    const c = document.getElementById('filesContainer');
    const div = document.createElement('div');
    div.className = 'file-entry row g-2 mb-2 align-items-end';
    div.innerHTML = `
        <div class="col-md-4"><label class="form-label small">File Label</label>
            <input type="text" name="file_labels[]" class="form-control form-control-sm" placeholder="e.g. Bonus Pack"></div>
        <div class="col-md-6"><label class="form-label small">File (max 100MB)</label>
            <input type="file" name="files[]" class="form-control form-control-sm"></div>
        <div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm w-100"
            onclick="this.closest('.file-entry').remove()">Remove</button></div>`;
    c.appendChild(div);
}

function addVariantRow() {
    const c = document.getElementById('variantsContainer');
    const div = document.createElement('div');
    div.className = 'variant-entry row g-2 mb-2 align-items-end';
    div.innerHTML = `
        <div class="col-md-4"><label class="form-label small">Variant Name</label>
            <input type="text" name="variant_names[]" class="form-control form-control-sm" placeholder="e.g. Large / Black"></div>
        <div class="col-md-3"><label class="form-label small">SKU</label>
            <input type="text" name="variant_skus[]" class="form-control form-control-sm"></div>
        <div class="col-md-2"><label class="form-label small">Price (₦)</label>
            <input type="number" step="0.01" min="0" name="variant_prices[]" class="form-control form-control-sm" placeholder="base"></div>
        <div class="col-md-2"><label class="form-label small">Stock</label>
            <input type="number" min="0" name="variant_stocks[]" class="form-control form-control-sm"></div>
        <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm w-100"
            onclick="this.closest('.variant-entry').remove()">&times;</button></div>`;
    c.appendChild(div);
}

// Toggle on radio change (create) and on initial load (both create & edit).
document.querySelectorAll('input[name=kind]').forEach(el => el.addEventListener('change', syncKind));
syncKind();
</script>
@endpush
