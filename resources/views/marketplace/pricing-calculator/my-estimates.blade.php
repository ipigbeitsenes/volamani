@extends('layouts.account')

@section('title', 'My Pricing Estimates')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">My Pricing Estimates</h4>
        <a href="{{ route('pricing-calculator.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> New Estimate
        </a>
    </div>

    @if ($estimates->isEmpty())
        <div class="text-center py-5 text-muted border rounded">
            <i class="bi bi-calculator fs-1 d-block mb-3"></i>
            <p>No saved estimates yet.</p>
            <a href="{{ route('pricing-calculator.index') }}" class="btn btn-primary">
                Create Your First Estimate
            </a>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Service</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Urgency</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($estimates as $estimate)
                            <tr>
                                <td><code>{{ $estimate->reference }}</code></td>
                                <td>{{ Str::limit($estimate->service_name, 40) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $estimate->category->label() }}</span></td>
                                <td class="text-muted small">{{ $estimate->pricing_type->label() }}</td>
                                <td>
                                    <span class="badge bg-{{ $estimate->urgencyBadge() }}">
                                        {{ ucfirst($estimate->urgency) }}
                                        @if ($estimate->urgency !== 'normal')
                                            (×{{ $estimate->urgency_multiplier }})
                                        @endif
                                    </span>
                                </td>
                                <td class="fw-bold text-success">{{ money($estimate->total) }}</td>
                                <td class="text-muted small">{{ $estimate->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('pricing-calculator.show', $estimate->reference) }}"
                                       class="btn btn-outline-primary btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($estimates->hasPages())
                <div class="card-footer bg-white">{{ $estimates->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
