@extends('layouts.app')

@section('title', $document->number)

@section('content')
<div class="container py-4" style="max-width: 820px;">
    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
        @if(session($key))<div class="alert alert-{{ $variant }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0">{{ $document->number }}</h4>
                <span class="badge bg-{{ ($document->isOverdue() ? \App\Enums\DocumentStatus::Overdue : $document->status)->badge() }}">{{ $document->isOverdue() ? 'Overdue' : $document->status->label() }}</span>
            </div>
            <p class="text-muted mb-0">{{ $document->type->label() }} from {{ $document->vendor->business_name }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('invoices.download', $document) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download me-1"></i>Download</a>

            @if($document->isQuotation() && in_array($document->status, [\App\Enums\DocumentStatus::Sent, \App\Enums\DocumentStatus::Viewed]))
                <form action="{{ route('invoices.accept', $document) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-sm btn-success">Accept</button>
                </form>
                <form action="{{ route('invoices.decline', $document) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-sm btn-outline-danger">Decline</button>
                </form>
            @endif

            @if($document->isInvoice() && $document->balanceDue() > 0 && $document->status !== \App\Enums\DocumentStatus::Cancelled)
                <form action="{{ route('invoices.pay', $document) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-sm btn-primary"><i class="bi bi-credit-card me-1"></i>Pay {{ money($document->balanceDue()) }}</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6">
                    <div class="small text-muted">From</div>
                    <div class="fw-semibold">{{ $document->vendor->business_name }}</div>
                </div>
                <div class="col-6 text-end">
                    <div class="small text-muted">Issued</div>
                    <div>{{ $document->issue_date?->format('d M Y') ?? '—' }}</div>
                    @if($document->isInvoice())
                        <div class="small text-muted mt-1">Due</div><div>{{ $document->due_date?->format('d M Y') ?? '—' }}</div>
                    @else
                        <div class="small text-muted mt-1">Valid until</div><div>{{ $document->valid_until?->format('d M Y') ?? '—' }}</div>
                    @endif
                </div>
            </div>

            <table class="table align-middle">
                <thead class="table-light">
                    <tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    @foreach($document->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td class="text-end">{{ money($item->unit_price) }}</td>
                            <td class="text-end">{{ money($item->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                <table class="table table-sm w-auto mb-0">
                    <tr><td class="text-muted">Subtotal</td><td class="text-end ps-4">{{ money($document->subtotal) }}</td></tr>
                    @if($document->discount_amount > 0)<tr><td class="text-muted">Discount</td><td class="text-end ps-4">−{{ money($document->discount_amount) }}</td></tr>@endif
                    @if($document->tax_amount > 0)<tr><td class="text-muted">Tax</td><td class="text-end ps-4">{{ money($document->tax_amount) }}</td></tr>@endif
                    <tr class="fw-bold border-top"><td>Total</td><td class="text-end ps-4">{{ money($document->total) }}</td></tr>
                    @if($document->isInvoice() && $document->amount_paid > 0)
                        <tr><td class="text-muted">Paid</td><td class="text-end ps-4 text-success">−{{ money($document->amount_paid) }}</td></tr>
                        <tr class="fw-bold"><td>Balance due</td><td class="text-end ps-4">{{ money($document->balanceDue()) }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if($document->notes || $document->terms)
        <div class="card border-0 shadow-sm">
            <div class="card-body small">
                @if($document->notes)<p class="mb-2"><strong>Notes:</strong> {{ $document->notes }}</p>@endif
                @if($document->terms)<p class="mb-0"><strong>Terms:</strong> {{ $document->terms }}</p>@endif
            </div>
        </div>
    @endif
</div>
@endsection
