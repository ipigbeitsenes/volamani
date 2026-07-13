@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h4 class="fw-bold mb-3"><i class="bi bi-chat-dots me-2"></i>Messages</h4>

    @forelse($conversations as $c)
        <a href="{{ route('messages.show', $c) }}" class="card border-0 shadow-sm mb-2 text-decoration-none text-dark">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="feature-tile sm bg-primary bg-opacity-10 text-primary flex-shrink-0"><i class="bi bi-person"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold">{{ $c->counterpartName($user) }}</div>
                    <div class="text-muted small text-truncate">{{ $c->latestMessage?->body ?? 'No messages yet' }}</div>
                    @if($c->product)
                        <div class="text-muted small text-truncate"><i class="bi bi-box-seam me-1"></i>{{ $c->product->name }}</div>
                    @endif
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="text-muted small">{{ $c->last_message_at?->diffForHumans() }}</div>
                    @php $unread = $c->unreadCountFor($user); @endphp
                    @if($unread)<span class="badge bg-primary rounded-pill mt-1">{{ $unread }}</span>@endif
                </div>
            </div>
        </a>
    @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-chat-dots" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-0">No messages yet. Start one from any product page.</p>
        </div>
    @endforelse

    <div class="mt-3">{{ $conversations->links() }}</div>
</div>
@endsection
