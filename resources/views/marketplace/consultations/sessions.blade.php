@extends('layouts.account')

@section('title', 'My Consultation Sessions')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">My Consultation Sessions</h4>

    @if ($sessions->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
            <p>You haven't booked any consultations yet.</p>
            <a href="{{ route('marketplace.consultants.index') }}" class="btn btn-primary">Find a Consultant</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Consultant</th>
                        <th>Package</th>
                        <th>Scheduled</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sessions as $session)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $session->profile->display_name }}</div>
                                <small class="text-muted">{{ $session->profile->niche }}</small>
                            </td>
                            <td>
                                <div>{{ $session->package->name }}</div>
                                <small class="text-muted">{{ $session->package->durationLabel() }}</small>
                            </td>
                            <td>
                                <div>{{ $session->scheduled_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $session->scheduled_at->format('g:i A') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $session->status->badge() }}">{{ $session->status->label() }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $session->payment_status->badge() }}">{{ $session->payment_status->label() }}</span>
                            </td>
                            <td>
                                <a href="{{ route('consultations.sessions.show', $session) }}" class="btn btn-outline-primary btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $sessions->links() }}</div>
    @endif
</div>
@endsection
