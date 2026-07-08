@extends('layouts.admin')

@section('title', 'Live Chat')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Live Chat</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Live Chat</h4>
        <p class="text-muted mb-0 small">
            Answer visitor conversations in real time.
            @if($openCount > 0)
                <span class="text-danger fw-semibold">{{ $openCount }} awaiting a reply.</span>
            @else
                All caught up.
            @endif
        </p>
    </div>
    <a href="{{ route('admin.live-chat.settings') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-gear me-1"></i>Widget Settings
    </a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Name, email or subject...">
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($conversations->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-chat-dots fs-2 d-block mb-2"></i>No conversations yet.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Visitor</th>
                            <th class="small">Last message</th>
                            <th class="small">Status</th>
                            <th class="small">Agent</th>
                            <th class="small">Activity</th>
                            <th class="small text-end">Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conversations as $c)
                        <tr class="{{ $c->agent_unread > 0 ? 'table-warning' : '' }}">
                            <td>
                                <div class="fw-medium">
                                    {{ $c->visitorName() }}
                                    @if($c->agent_unread > 0)
                                        <span class="badge bg-danger rounded-pill ms-1">{{ $c->agent_unread }}</span>
                                    @endif
                                </div>
                                <div class="text-muted small">
                                    {{ $c->visitorEmail() ?? 'No email' }}
                                    @if(! $c->user_id)<span class="badge bg-light text-muted border ms-1">Guest</span>@endif
                                </div>
                            </td>
                            <td class="small text-muted" style="max-width:260px">
                                <span class="d-inline-block text-truncate" style="max-width:250px">
                                    {{ $c->latestMessage?->body ?? '—' }}
                                </span>
                            </td>
                            <td><span class="badge bg-{{ $c->status->badge() }}">{{ $c->status->label() }}</span></td>
                            <td class="small">{{ $c->agent?->name ?? '—' }}</td>
                            <td class="small text-muted">
                                {{ ($c->last_visitor_at ?? $c->created_at)->diffForHumans() }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.live-chat.show', $c) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-chat-right-text"></i> Open
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $conversations->links() }}</div>
        @endif
    </div>
</div>
@endsection
