@extends('layouts.admin')

@section('title', 'Vendors')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4">Vendors</h4>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-5">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Business name or owner email…">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(\App\Enums\Status::cases() as $s)
                    <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>
                        {{ $s->label() }} ({{ $counts[$s->value] ?? 0 }})
                    </option>
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
                        <tr><th>Store</th><th>Owner</th><th>Plan</th><th>Joined</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $vendor->logo_url }}" class="rounded bg-white border" width="36" height="36" style="object-fit:contain;padding:1px" alt="">
                                        <div class="fw-semibold">{{ $vendor->business_name }}</div>
                                    </div>
                                </td>
                                <td class="small">{{ $vendor->user->name ?? '—' }}<br><span class="text-muted">{{ $vendor->user->email ?? '' }}</span></td>
                                <td class="small">{{ $vendor->plan ? ucfirst($vendor->plan) : 'Free' }}</td>
                                <td class="small text-muted">{{ $vendor->created_at->format('d M Y') }}</td>
                                <td><span class="badge bg-{{ $vendor->status->badge() }}-subtle text-{{ $vendor->status->badge() }}">{{ $vendor->status->label() }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-sm btn-outline-secondary">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No vendors found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $vendors->withQueryString()->links() }}</div>
</div>
@endsection
