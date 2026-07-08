@extends('layouts.account')

@section('title', 'Session #' . $session->reference)

@section('content')
<div class="container py-4" style="max-width:800px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Session {{ $session->reference }}</h4>
            <small class="text-muted">{{ $session->package->name }} with {{ $session->profile->display_name }}</small>
        </div>
        <span class="badge bg-{{ $session->status->badge() }} fs-6">{{ $session->status->label() }}</span>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            {{-- Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Session Details</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Consultant</dt>
                        <dd class="col-sm-7">{{ $session->profile->display_name }}</dd>

                        <dt class="col-sm-5">Package</dt>
                        <dd class="col-sm-7">{{ $session->package->name }} ({{ $session->package->durationLabel() }})</dd>

                        <dt class="col-sm-5">Scheduled</dt>
                        <dd class="col-sm-7">{{ $session->scheduled_at->format('D, d M Y g:i A') }}</dd>

                        @if ($session->meeting_link)
                            <dt class="col-sm-5">Meeting Link</dt>
                            <dd class="col-sm-7">
                                <a href="{{ $session->meeting_link }}" target="_blank" rel="noopener" class="text-primary">
                                    <i class="bi bi-camera-video me-1"></i>Join Meeting
                                </a>
                            </dd>
                        @endif

                        @if ($session->meeting_platform)
                            <dt class="col-sm-5">Platform</dt>
                            <dd class="col-sm-7">{{ ucwords(str_replace('_', ' ', $session->meeting_platform)) }}</dd>
                        @endif

                        @if ($session->notes)
                            <dt class="col-sm-5">Your Agenda</dt>
                            <dd class="col-sm-7">{{ $session->notes }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Payment --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Payment</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Amount</dt>
                        <dd class="col-sm-7">{{ money($session->price) }}</dd>
                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-{{ $session->payment_status->badge() }}">{{ $session->payment_status->label() }}</span>
                        </dd>
                    </dl>

                    @if ($session->payment_status->value === 'pending')
                        <hr>
                        <a href="{{ route('checkout.consultation', $session) }}" class="btn btn-success">
                            <i class="bi bi-credit-card me-1"></i> Pay Now to Confirm
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-5">
            {{-- Actions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Actions</h5>

                    @if ($isBuyer && $session->canBeCancelled())
                        <form method="POST" action="{{ route('consultations.sessions.cancel', $session) }}"
                            onsubmit="return confirm('Cancel this session?')">
                            @csrf
                            <div class="mb-2">
                                <textarea name="reason" class="form-control form-control-sm" rows="2" required
                                    placeholder="Reason for cancellation…"></textarea>
                            </div>
                            <button class="btn btn-outline-danger btn-sm w-100">Cancel Session</button>
                        </form>
                    @endif

                    @if ($isConsultant && $session->canBeConfirmed())
                        <div class="alert alert-warning small">Payment received. Confirm the session and send meeting details.</div>
                        <a href="{{ route('vendor.consultations.sessions.show', $session) }}" class="btn btn-primary btn-sm w-100">
                            Go to Vendor Panel →
                        </a>
                    @endif

                    @if ($session->status->value === 'completed')
                        <div class="alert alert-success small mb-0">
                            <i class="bi bi-check-circle me-1"></i> Session completed {{ $session->completed_at?->diffForHumans() }}.
                        </div>
                    @endif

                    @if ($session->status->value === 'cancelled')
                        <div class="alert alert-secondary small mb-0">
                            Session cancelled. {{ $session->cancellation_reason }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
