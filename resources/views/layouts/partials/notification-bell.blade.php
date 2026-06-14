{{-- Reusable notification bell for the light staff/vendor topbars. --}}
@auth
    @php
        $vlmUnread = auth()->user()->unreadNotifications()->count();
        $vlmRecent = auth()->user()->notifications()->limit(6)->get();
    @endphp
    <div class="dropdown">
        <button class="btn btn-sm position-relative" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Notifications">
            <i class="bi bi-bell fs-5"></i>
            @if($vlmUnread > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">
                    {{ $vlmUnread > 9 ? '9+' : $vlmUnread }}
                    <span class="visually-hidden">unread notifications</span>
                </span>
            @endif
        </button>
        <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 340px;">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <span class="fw-semibold small">Notifications</span>
                @if($vlmUnread > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}" class="m-0">
                        @csrf
                        <button class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
                    </form>
                @endif
            </div>
            <div style="max-height: 360px; overflow-y: auto;">
                @forelse($vlmRecent as $note)
                    @php
                        $d   = $note->data;
                        $cat = isset($d['category']) ? \App\Enums\NotificationCategory::tryFrom($d['category']) : null;
                    @endphp
                    <a href="{{ route('notifications.open', $note->id) }}"
                       class="dropdown-item d-flex gap-2 align-items-start py-2 {{ $note->read_at ? '' : 'bg-light' }}"
                       style="white-space: normal;">
                        <i class="bi {{ $d['icon'] ?? $cat?->icon() ?? 'bi-bell' }} text-{{ $cat?->color() ?? 'primary' }} mt-1"></i>
                        <span class="flex-grow-1">
                            <span class="d-block fw-semibold small">{{ $d['title'] ?? 'Notification' }}</span>
                            <span class="d-block text-muted" style="font-size:.78rem;">{{ \Illuminate\Support\Str::limit($d['message'] ?? '', 70) }}</span>
                            <span class="d-block text-muted" style="font-size:.7rem;">{{ $note->created_at->diffForHumans() }}</span>
                        </span>
                    </a>
                @empty
                    <div class="text-center text-muted small py-4">
                        <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>No notifications yet
                    </div>
                @endforelse
            </div>
            <a href="{{ route('notifications.index') }}" class="dropdown-item text-center border-top small py-2">
                View all notifications
            </a>
        </div>
    </div>
@endauth
