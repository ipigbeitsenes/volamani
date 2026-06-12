@extends('layouts.vendor')

@section('title', 'Consultation Sessions')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4">Consultation Sessions</h4>

    @if ($sessions->isEmpty())
        <div class="text-center py-5 text-muted border rounded">
            <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
            No sessions yet. Once clients book your packages, they'll appear here.
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Client</th>
                            <th>Package</th>
                            <th>Scheduled</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            <tr>
                                <td><code>{{ $session->reference }}</code></td>
                                <td>
                                    <div>{{ $session->buyer->name }}</div>
                                    <small class="text-muted">{{ $session->buyer->email }}</small>
                                </td>
                                <td>{{ $session->package->name }}</td>
                                <td>
                                    <div>{{ $session->scheduled_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $session->scheduled_at->format('g:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $session->status->badge() }}">{{ $session->status->label() }}</span>
                                </td>
                                <td>{{ money($session->consultant_earnings) }}</td>
                                <td>
                                    <a href="{{ route('vendor.consultations.sessions.show', $session) }}"
                                       class="btn btn-outline-primary btn-sm">Manage</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($sessions->hasPages())
                <div class="card-footer bg-white">{{ $sessions->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
