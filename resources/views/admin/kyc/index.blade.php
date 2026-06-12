@extends('layouts.admin')

@section('title', 'KYC Management')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4">KYC Verification Queue</h4>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search name, ID number, reference...">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(\App\Enums\KYCStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected(($filters['status'] ?? '') === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($verifications->isEmpty())
                <p class="text-muted text-center py-5 mb-0">No verifications found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>ID</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($verifications as $kyc)
                                <tr>
                                    <td class="font-monospace small">{{ $kyc->reference }}</td>
                                    <td>{{ $kyc->full_name }}<br><span class="text-muted small">{{ $kyc->user->email ?? '' }}</span></td>
                                    <td>{{ $kyc->type->label() }}</td>
                                    <td class="small">{{ $kyc->id_type->label() }}</td>
                                    <td class="text-muted small">{{ $kyc->submitted_at?->format('d M Y') ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $kyc->status->badge() }}-subtle text-{{ $kyc->status->badge() }}">{{ $kyc->status->label() }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.kyc.show', $kyc) }}" class="btn btn-sm btn-outline-secondary">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">{{ $verifications->withQueryString()->links() }}</div>
</div>
@endsection
