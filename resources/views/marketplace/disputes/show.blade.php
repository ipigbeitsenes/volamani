@extends('layouts.account')

@section('title', 'Support Ticket ' . $dispute->reference)

@section('content')
<div class="container py-4" style="max-width: 860px;">
    <a href="{{ route('disputes.index') }}" class="text-decoration-none small">&larr; Back to support tickets</a>

    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }} mt-3">{{ session($key) }}</div>@endif
    @endforeach

    <div class="card border-0 shadow-sm mt-3 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="fw-bold mb-1 font-monospace">{{ $dispute->reference }}</h5>
                    <p class="text-muted mb-0 small">
                        {{ $dispute->reason->label() }} ·
                        {{ class_basename($dispute->escrow->escrowable_type) }}
                        <span class="font-monospace">{{ $dispute->escrow->escrowable?->reference }}</span> ·
                        {{ money($dispute->escrow->total_amount) }}
                    </p>
                </div>
                <span class="badge bg-{{ $dispute->status->badge() }}-subtle text-{{ $dispute->status->badge() }} fs-6">
                    {{ $dispute->status->label() }}
                </span>
            </div>

            @if($dispute->isResolved() && $dispute->resolution)
                <div class="alert alert-{{ $dispute->resolution->badge() }} mt-3 mb-0">
                    <strong>Resolved:</strong> {{ $dispute->resolution->label() }}.
                    {{ $dispute->resolution->description() }}
                    @if($dispute->resolution_note)<div class="small mt-1">{{ $dispute->resolution_note }}</div>@endif
                </div>
            @elseif($dispute->slaCountdownLabel())
                <div class="alert alert-{{ $dispute->isSlaOverdue() ? 'warning' : 'light' }} border mt-3 mb-0 small">
                    <i class="bi bi-clock-history me-1"></i>Awaiting a response — {{ $dispute->slaCountdownLabel() }}. If it stays unanswered our team will step in automatically.
                </div>
            @endif
        </div>
    </div>

    {{-- Conversation --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Conversation</div>
        <div class="card-body">
            @foreach($dispute->messages as $msg)
                @if($msg->is_system)
                    <div class="text-center text-muted small my-2">{{ $msg->message }}</div>
                @else
                    <div class="d-flex mb-3 {{ $msg->sender_id === auth()->id() ? 'justify-content-end' : '' }}">
                        <div class="p-3 rounded-3 {{ $msg->is_staff ? 'bg-warning-subtle' : ($msg->sender_id === auth()->id() ? 'bg-primary-subtle' : 'bg-light') }}" style="max-width: 75%;">
                            <div class="small fw-semibold mb-1">
                                {{ $msg->is_staff ? 'Support Team' : ($msg->sender?->name ?? 'User') }}
                                <span class="text-muted fw-normal">· {{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                            <div>{{ $msg->message }}</div>
                            @if($msg->attachment)
                                <a href="{{ $msg->attachmentUrl() }}" target="_blank" class="small d-inline-block mt-1">
                                    📎 {{ $msg->attachment_name ?? 'Attachment' }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @if($dispute->isOpen())
            <div class="card-footer bg-white">
                <form action="{{ route('disputes.message', $dispute) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <textarea name="message" rows="2" class="form-control @error('message') is-invalid @enderror"
                                  placeholder="Add a message…">{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <input type="file" name="attachment" class="form-control form-control-sm w-auto">
                        <button class="btn btn-primary btn-sm">Send</button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
