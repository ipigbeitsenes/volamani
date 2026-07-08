@extends('layouts.account')

@section('title', 'My Invoices')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Invoices &amp; Quotations</h4>
        <p class="text-muted mb-0">Documents sent to you by vendors you work with.</p>
    </div>

    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($documents->isEmpty())
                <p class="text-muted text-center py-5 mb-0">You haven't received any invoices or quotations yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Number</th><th>From</th><th>Type</th><th class="text-end">Total</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr>
                                    <td><code>{{ $doc->number }}</code></td>
                                    <td>{{ $doc->vendor->business_name ?? '—' }}</td>
                                    <td><i class="bi {{ $doc->type->icon() }} me-1"></i>{{ $doc->type->label() }}</td>
                                    <td class="text-end">{{ money($doc->total) }}</td>
                                    <td><span class="badge bg-{{ ($doc->isOverdue() ? \App\Enums\DocumentStatus::Overdue : $doc->status)->badge() }}">{{ $doc->isOverdue() ? 'Overdue' : $doc->status->label() }}</span></td>
                                    <td class="text-end"><a href="{{ route('invoices.show', $doc) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">{{ $documents->links() }}</div>
</div>
@endsection
