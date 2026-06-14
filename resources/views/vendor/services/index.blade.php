@extends('layouts.vendor')

@section('title', 'My Services')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">My Services</h4>
    <a href="{{ route('vendor.services.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create Service
    </a>
</div>

@if($services->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-briefcase fs-1 text-muted"></i>
            <h5 class="mt-3">No services yet</h5>
            <p class="text-muted">Create your first freelance service and start earning.</p>
            <a href="{{ route('vendor.services.create') }}" class="btn btn-primary">Create Your First Service</a>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Service</th>
                        <th>Category</th>
                        <th>Starting Price</th>
                        <th>Status</th>
                        <th>Orders</th>
                        <th>Rating</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $service)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $service->thumbnail_url }}"
                                         class="rounded bg-light border" style="width:48px;height:48px;object-fit:contain;">
                                    <span class="fw-semibold">{{ Str::limit($service->title, 50) }}</span>
                                </div>
                            </td>
                            <td class="text-muted small">{{ $service->category->name ?? '—' }}</td>
                            <td class="fw-semibold">{{ money($service->lowestPrice()) }}</td>
                            <td>
                                <span class="badge bg-{{ $service->status->badge() }}">
                                    {{ $service->status->label() }}
                                </span>
                            </td>
                            <td>{{ number_format($service->orders_count) }}</td>
                            <td>
                                @if($service->reviews_count > 0)
                                    <i class="bi bi-star-fill text-warning"></i>
                                    {{ number_format($service->average_rating, 1) }}
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown">Actions</button>
                                    <ul class="dropdown-menu">
                                        @if($service->isActive())
                                            <li>
                                                <a class="dropdown-item" href="{{ route('marketplace.services.show', $service->slug) }}" target="_blank">
                                                    <i class="bi bi-eye me-2"></i>View Listing
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="{{ route('vendor.services.edit', $service->id) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('vendor.services.destroy', $service->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Archive this service?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-archive me-2"></i>Archive
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $services->links() }}</div>
@endif
@endsection
