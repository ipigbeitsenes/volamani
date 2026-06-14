@extends('layouts.vendor')

@section('title', 'My Quotations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">My Quotations</h4>
    <a href="{{ route('marketplace.requests.index') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-search me-1"></i> Browse Requests
    </a>
</div>

{{-- Direct requests sent to this store --}}
@if(!empty($directRequests) && $directRequests->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4 border-start border-warning border-3">
        <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-envelope-paper text-warning"></i>
            Direct requests sent to you
            <span class="badge bg-warning text-dark">{{ $directRequests->count() }}</span>
        </div>
        <ul class="list-group list-group-flush">
            @foreach($directRequests as $dr)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('marketplace.requests.show', $dr->id) }}" class="fw-semibold text-decoration-none">{{ Str::limit($dr->title, 60) }}</a>
                        <div class="small text-muted">
                            {{ $dr->buyer->name ?? 'A buyer' }} · {{ $dr->budgetRange() }} · {{ $dr->created_at->diffForHumans() }}
                            @if($dr->hasQuotedBy(auth()->user()->vendor))<span class="badge bg-success ms-1">Quoted</span>@endif
                        </div>
                    </div>
                    <a href="{{ route('marketplace.requests.show', $dr->id) }}" class="btn btn-sm btn-primary">
                        {{ $dr->hasQuotedBy(auth()->user()->vendor) ? 'View' : 'Send quotation' }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if($quotations->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-file-text fs-1 text-muted"></i>
            <h5 class="mt-3">No quotations yet</h5>
            <p class="text-muted">Browse open buyer requests and submit your first quotation.</p>
            <a href="{{ route('marketplace.requests.index') }}" class="btn btn-primary">Browse Requests</a>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Request</th>
                        <th>Buyer</th>
                        <th>Your Price</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotations as $quotation)
                        <tr>
                            <td>
                                <a href="{{ route('marketplace.requests.show', $quotation->request_id) }}"
                                   class="text-dark text-decoration-none fw-semibold small">
                                    {{ Str::limit($quotation->request->title, 45) }}
                                </a>
                                @if($quotation->request->category)
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $quotation->request->category->name }}</div>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $quotation->request->buyer->name }}</td>
                            <td class="fw-semibold">{{ money($quotation->price) }}</td>
                            <td class="small">{{ $quotation->delivery_days }}d</td>
                            <td>
                                <span class="badge bg-{{ $quotation->status->badge() }}">
                                    {{ $quotation->status->label() }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $quotation->created_at->format('M j, Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown">Actions</button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item"
                                               href="{{ route('marketplace.requests.show', $quotation->request_id) }}">
                                                <i class="bi bi-eye me-2"></i>View Request
                                            </a>
                                        </li>
                                        @if($quotation->canBeWithdrawn())
                                            <li>
                                                <form action="{{ route('vendor.quotations.withdraw', $quotation->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Withdraw this quotation?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-x-circle me-2"></i>Withdraw
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $quotations->links() }}</div>
@endif
@endsection
