@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container py-4" style="max-width: 820px;">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Notifications</h1>
            <p class="text-muted small mb-0">
                {{ $unread }} unread {{ Str::plural('notification', $unread) }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('notifications.preferences') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-sliders me-1"></i>Preferences
            </a>
            @if($unread > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-check2-all me-1"></i>Mark all read</button>
                </form>
            @endif
            @if($notifications->total() > 0)
                <form method="POST" action="{{ route('notifications.clear') }}"
                      onsubmit="return confirm('Delete all notifications? This cannot be undone.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Clear all</button>
                </form>
            @endif
        </div>
    </div>

    <ul class="nav nav-pills gap-2 mb-3">
        <li class="nav-item">
            <a class="nav-link {{ ! $filter ? 'active' : '' }}" href="{{ route('notifications.index') }}">All</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $filter === 'unread' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'unread']) }}">Unread</a>
        </li>
    </ul>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse($notifications as $note)
                @php
                    $data     = $note->data;
                    $category = isset($data['category']) ? \App\Enums\NotificationCategory::tryFrom($data['category']) : null;
                    $color    = $category?->color() ?? 'primary';
                    $icon     = $data['icon'] ?? $category?->icon() ?? 'bi-bell';
                @endphp
                <div class="list-group-item d-flex gap-3 align-items-start {{ $note->read_at ? '' : 'bg-light' }}">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $color }} bg-opacity-10 text-{{ $color }} flex-shrink-0"
                          style="width: 42px; height: 42px;">
                        <i class="bi {{ $icon }}"></i>
                    </span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <a href="{{ route('notifications.open', $note->id) }}" class="text-decoration-none text-dark">
                                <span class="fw-semibold">{{ $data['title'] ?? 'Notification' }}</span>
                                @unless($note->read_at)
                                    <span class="badge bg-primary rounded-pill ms-1" style="font-size: .55rem;">NEW</span>
                                @endunless
                            </a>
                            <small class="text-muted text-nowrap">{{ $note->created_at->diffForHumans() }}</small>
                        </div>
                        @if(! empty($data['message']))
                            <p class="text-muted small mb-1">{{ $data['message'] }}</p>
                        @endif
                        <div class="d-flex gap-3">
                            @unless($note->read_at)
                                <form method="POST" action="{{ route('notifications.read', $note->id) }}">
                                    @csrf
                                    <button class="btn btn-link btn-sm p-0 text-decoration-none">Mark read</button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('notifications.destroy', $note->id) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-link btn-sm p-0 text-decoration-none text-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash display-5 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">You're all caught up — no notifications here.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">
        {{ $notifications->withQueryString()->links() }}
    </div>
</div>
@endsection
