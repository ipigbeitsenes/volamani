@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-1">Audit Logs</h4>
    <p class="text-muted small mb-4">A chronological trail of changes recorded across the platform.</p>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-5">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search description…">
        </div>
        <div class="col-md-4">
            <select name="log" class="form-select">
                <option value="">All log channels</option>
                @foreach($logNames as $name)
                    <option value="{{ $name }}" @selected(($filters['log'] ?? '') === $name)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-primary w-100">Filter</button></div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>When</th><th>Description</th><th>Subject</th><th>By</th></tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="small text-muted text-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ ucfirst($log->description) }}</span>
                                    @if($log->event)<span class="badge bg-secondary-subtle text-secondary ms-1">{{ $log->event }}</span>@endif
                                    @if($log->log_name)<span class="badge bg-light text-muted ms-1">{{ $log->log_name }}</span>@endif
                                </td>
                                <td class="small text-muted">{{ $log->subject_type ? class_basename($log->subject_type) . ' #' . $log->subject_id : '—' }}</td>
                                <td class="small">{{ $log->causer->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-5">No activity recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $logs->withQueryString()->links() }}</div>
</div>
@endsection
