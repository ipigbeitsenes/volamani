@extends('layouts.app')

@section('title', 'Message seller')

@section('content')
<div class="container py-4" style="max-width: 620px;">
    <h5 class="fw-bold mb-3">Message {{ $vendor->business_name }}</h5>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded">
                <img src="{{ $product->thumbnail_url }}" width="48" height="48" class="rounded" style="object-fit:cover;" alt="">
                <div>
                    <div class="fw-semibold small">{{ $product->name }}</div>
                    <div class="text-muted small">{{ money($product->price) }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('messages.start') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <textarea name="body" rows="4" maxlength="2000" required autofocus
                          class="form-control @error('body') is-invalid @enderror"
                          placeholder="Hi, I'm interested in this item…">{{ old('body') }}</textarea>
                @error('body')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                <button class="btn btn-primary mt-3"><i class="bi bi-send me-1"></i>Send message</button>
            </form>
        </div>
    </div>
</div>
@endsection
