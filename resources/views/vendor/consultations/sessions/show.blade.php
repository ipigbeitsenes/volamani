@extends('layouts.vendor')

@section('title', 'Session ' . $session->reference)

@section('content')
<div class="container py-4" style="max-width:800px">
    <a href="{{ route('vendor.consultations.sessions') }}" class="btn btn-link btn-sm ps-0 mb-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Sessions
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">{{ $session->reference }}</h4>
            <small class="text-muted">{{ $session->package->name }} • {{ $session->buyer->name }}</small>
        </div>
        <span class="badge bg-{{ $session->status->badge() }} fs-6">{{ $session->status->label() }}</span>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            {{-- Details card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Session Details</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Client</dt>
                        <dd class="col-sm-7">{{ $session->buyer->name }}<br><small class="text-muted">{{ $session->buyer->email }}</small></dd>

                        <dt class="col-sm-5">Package</dt>
                        <dd class="col-sm-7">{{ $session->package->name }} ({{ $session->package->durationLabel() }})</dd>

                        <dt class="col-sm-5">Scheduled</dt>
                        <dd class="col-sm-7">{{ $session->scheduled_at->format('D, d M Y g:i A') }}</dd>

                        <dt class="col-sm-5">Payment</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-{{ $session->payment_status->badge() }}">{{ $session->payment_status->label() }}</span>
                        </dd>

                        <dt class="col-sm-5">Your Earnings</dt>
                        <dd class="col-sm-7">{{ money($session->consultant_earnings) }}</dd>

                        @if ($session->notes)
                            <dt class="col-sm-5">Client Agenda</dt>
                            <dd class="col-sm-7">{{ $session->notes }}</dd>
                        @endif

                        @if ($session->meeting_link)
                            <dt class="col-sm-5">Meeting Link</dt>
                            <dd class="col-sm-7"><a href="{{ $session->meeting_link }}" target="_blank">{{ $session->meeting_link }}</a></dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Consultant notes (completed only) --}}
            @if ($session->consultant_notes)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Your Notes</h5>
                        <p class="mb-0">{{ $session->consultant_notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-5">
            {{-- Actions --}}
            @if ($session->canBeConfirmed())
                <div class="card border-0 shadow-sm mb-4 border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Confirm Session</h5>
                        <p class="small text-muted">Payment received. Send the meeting link to confirm.</p>
                        <form method="POST" action="{{ route('vendor.consultations.sessions.confirm', $session) }}">
                            @csrf
                            <div class="mb-2">
                                <input type="url" name="meeting_link"
                                    class="form-control form-control-sm @error('meeting_link') is-invalid @enderror"
                                    placeholder="https://meet.google.com/..." required>
                                @error('meeting_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-2">
                                <select name="meeting_platform" class="form-select form-select-sm">
                                    <option value="">Platform (optional)</option>
                                    <option value="google_meet">Google Meet</option>
                                    <option value="zoom">Zoom</option>
                                    <option value="teams">Microsoft Teams</option>
                                    <option value="phone">Phone Call</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <button class="btn btn-primary btn-sm w-100">Confirm & Send Link</button>
                        </form>
                    </div>
                </div>
            @endif

            @if ($session->canBeCompleted())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Mark as Complete</h5>
                        <form method="POST" action="{{ route('vendor.consultations.sessions.complete', $session) }}">
                            @csrf
                            <div class="mb-2">
                                <textarea name="consultant_notes" rows="3"
                                    class="form-control form-control-sm"
                                    placeholder="Optional private notes about this session…"></textarea>
                            </div>
                            <button class="btn btn-success btn-sm w-100">Mark Completed</button>
                        </form>
                    </div>
                </div>
            @endif

            @if ($session->canBeCancelled())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Cancel Session</h5>
                        <form method="POST" action="{{ route('vendor.consultations.sessions.cancel', $session) }}"
                            onsubmit="return confirm('Cancel this session?')">
                            @csrf
                            <div class="mb-2">
                                <textarea name="reason" rows="2"
                                    class="form-control form-control-sm @error('reason') is-invalid @enderror"
                                    placeholder="Reason for cancellation…" required></textarea>
                                @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <button class="btn btn-outline-danger btn-sm w-100">Cancel Session</button>
                        </form>
                    </div>
                </div>
            @endif

            @if ($session->status->value === 'completed')
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-1"></i> Session completed {{ $session->completed_at?->diffForHumans() }}.
                </div>
            @endif

            @if ($session->status->value === 'cancelled')
                <div class="alert alert-secondary">
                    <strong>Cancelled:</strong> {{ $session->cancellation_reason }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
