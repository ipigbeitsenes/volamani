@extends('layouts.vendor')

@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('vendor.products.index') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
        <h4 class="fw-bold mb-0 mt-1">Edit: {{ Str::limit($product->name, 50) }}</h4>
    </div>
    <span class="badge bg-{{ $product->status->badge() }} fs-6">{{ $product->status->label() }}</span>
</div>

@if($product->status->value === 'rejected' && $product->rejection_reason)
    <div class="alert alert-danger mb-4">
        <strong>Rejection Reason:</strong> {{ $product->rejection_reason }}
    </div>
@endif

<form action="{{ route('vendor.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('vendor.products._form')

    {{-- Existing Gallery --}}
    @if($product->gallery->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white fw-semibold">Current Gallery</div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($product->gallery as $img)
                        <div class="position-relative">
                            <img src="{{ $img->url }}"
                                 class="rounded border bg-light"
                                 style="width:100px;height:100px;object-fit:contain;">
                            <button type="button"
                                class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 lh-1"
                                style="width:20px;height:20px;font-size:11px;"
                                onclick="deleteGalleryImage({{ $img->id }}, this)">
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Existing Files --}}
    @if($product->files->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white fw-semibold">Current Files</div>
            <div class="card-body">
                @foreach($product->files as $file)
                    <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2" id="file_{{ $file->id }}">
                        <div>
                            <i class="bi bi-file-earmark me-2"></i>
                            {{ $file->label }}
                            <small class="text-muted ms-2">({{ $file->file_size_formatted }})</small>
                        </div>
                        <button type="button"
                            class="btn btn-outline-danger btn-sm"
                            onclick="deleteProductFile({{ $file->id }}, this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i> Save Changes
        </button>
        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

async function deleteGalleryImage(imageId, btn) {
    if (!confirm('Remove this image?')) return;
    const res = await fetch(`/vendor/products/gallery/${imageId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    });
    if (res.ok) btn.closest('.position-relative').remove();
    else alert('Could not delete image.');
}

async function deleteProductFile(fileId, btn) {
    if (!confirm('Remove this file?')) return;
    const res = await fetch(`/vendor/products/files/${fileId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    });
    if (res.ok) document.getElementById(`file_${fileId}`).remove();
    else alert('Could not delete file.');
}
</script>
@endpush
@endsection
