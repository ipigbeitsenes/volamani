@extends('layouts.app')

@section('title', 'Conversation')

@section('content')
<div class="container py-4" style="max-width: 760px;">
    <a href="{{ route('messages.index') }}" class="btn btn-sm btn-link text-decoration-none mb-2"><i class="bi bi-arrow-left"></i> Inbox</a>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <div class="fw-bold">{{ $conversation->counterpartName($user) }}</div>
            @if($conversation->product)
                <a href="{{ route('marketplace.products.show', $conversation->product->slug) }}" class="small text-muted text-decoration-none">
                    <i class="bi bi-box-seam me-1"></i>{{ $conversation->product->name }}
                </a>
            @endif
        </div>

        <div class="card-body" style="max-height:60vh; overflow-y:auto;">
            @forelse($messages as $m)
                @php $mine = $m->sender_id === $user->id; @endphp
                <div class="d-flex mb-2 {{ $mine ? 'justify-content-end' : '' }}">
                    <div class="p-2 px-3 rounded-3 {{ $mine ? 'bg-primary text-white' : 'bg-light' }}" style="max-width:75%;">
                        <div style="white-space:pre-wrap;">{{ $m->body }}</div>
                        <div class="small {{ $mine ? 'text-white-50' : 'text-muted' }} mt-1">{{ $m->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">No messages yet — say hello below.</div>
            @endforelse
        </div>

        <div class="card-footer bg-white">
            <form method="POST" action="{{ route('messages.reply', $conversation) }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="body" class="form-control @error('body') is-invalid @enderror"
                       placeholder="Type a message…" maxlength="2000" required autofocus>
                <button class="btn btn-primary"><i class="bi bi-send"></i></button>
            </form>
            @error('body')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
@endsection
