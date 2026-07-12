@extends($docLayout ?? 'layouts.vendor')

@section('title', $document->number)

@section('content')
<div class="container-fluid py-4" style="max-width: 920px;">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0">{{ $document->number }}</h4>
                <span class="badge bg-{{ ($document->isOverdue() ? \App\Enums\DocumentStatus::Overdue : $document->status)->badge() }}">
                    {{ $document->isOverdue() ? 'Overdue' : $document->status->label() }}
                </span>
            </div>
            <p class="text-muted mb-0">{{ $type->label() }} · {{ $document->title ?: $document->client_name }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route($routeBase . '.print', $document) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer me-1"></i>Print</a>
            @if($document->isEditable())
                <a href="{{ route($routeBase . '.edit', $document) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
                <form action="{{ route($routeBase . '.send', $document) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-sm btn-primary"><i class="bi bi-send me-1"></i>Send</button>
                </form>
            @endif
            @if($document->isQuotation() && in_array($document->status, [\App\Enums\DocumentStatus::Sent, \App\Enums\DocumentStatus::Viewed, \App\Enums\DocumentStatus::Accepted]))
                <form action="{{ route($routeBase . '.convert', $document) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-sm btn-success"><i class="bi bi-arrow-right-circle me-1"></i>Convert to invoice</button>
                </form>
            @endif
            @if($document->convertedTo)
                <a href="{{ route('vendor.invoices.show', $document->convertedTo) }}" class="btn btn-sm btn-outline-success">View invoice {{ $document->convertedTo->number }}</a>
            @endif
        </div>
    </div>

    {{-- Shareable client link --}}
    @php $shareUrl = $document->publicUrl(); @endphp
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-link-45deg text-primary fs-5"></i>
                <h6 class="fw-bold mb-0">Share with client</h6>
                @if($document->sent_at)
                    <span class="badge bg-light text-success border ms-auto"><i class="bi bi-check2 me-1"></i>Sent {{ $document->sent_at->diffForHumans() }}</span>
                @endif
            </div>
            <p class="text-muted small mb-2">Anyone with this link can view{{ $document->isInvoice() ? ' and pay' : ($document->isContract() ? ' and sign' : ' and accept') }} this {{ strtolower($type->label()) }} — no account needed.</p>
            <div class="input-group input-group-sm mb-2">
                <input type="text" id="shareLink" class="form-control" value="{{ $shareUrl }}" readonly onclick="this.select()">
                <button class="btn btn-primary" type="button" onclick="vlCopyShare(this)"><i class="bi bi-clipboard me-1"></i>Copy</button>
                <a class="btn btn-outline-secondary" href="{{ $shareUrl }}" target="_blank"><i class="bi bi-box-arrow-up-right"></i></a>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-sm btn-outline-success" target="_blank"
                   href="https://wa.me/{{ preg_replace('/\D/', '', $document->client_phone ?? '') }}?text={{ rawurlencode($type->label() . ' ' . $document->number . ' from ' . $document->issuerName() . ': ' . $shareUrl) }}">
                    <i class="bi bi-whatsapp me-1"></i>WhatsApp
                </a>
                @if($document->client_email)
                    <a class="btn btn-sm btn-outline-secondary" href="mailto:{{ $document->client_email }}?subject={{ rawurlencode($type->label() . ' ' . $document->number) }}&body={{ rawurlencode('View your ' . strtolower($type->label()) . ': ' . $shareUrl) }}">
                        <i class="bi bi-envelope me-1"></i>Email
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if($document->isContract() && $document->isSigned())
        <div class="alert alert-success d-flex align-items-center gap-2 border-0 shadow-sm">
            <i class="bi bi-patch-check-fill fs-5"></i>
            <div class="small">
                Signed by <strong>{{ $document->signed_name }}</strong>
                on {{ $document->accepted_at?->format('d M Y, H:i') }}
                @if($document->signed_ip)<span class="text-muted">· IP {{ $document->signed_ip }}</span>@endif
            </div>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="small text-muted">Billed to</div>
                            <div class="fw-semibold">{{ $document->client_name }}</div>
                            @if($document->client_email)<div class="small">{{ $document->client_email }}</div>@endif
                            @if($document->client_phone)<div class="small">{{ $document->client_phone }}</div>@endif
                            @if($document->client_address)<div class="small text-muted">{{ $document->client_address }}</div>@endif
                        </div>
                        <div class="col-6 text-end">
                            <div class="small text-muted">Issued</div>
                            <div>{{ $document->issue_date?->format('d M Y') ?? '—' }}</div>
                            @if($document->isInvoice())
                                <div class="small text-muted mt-1">Due</div>
                                <div>{{ $document->due_date?->format('d M Y') ?? '—' }}</div>
                            @else
                                <div class="small text-muted mt-1">Valid until</div>
                                <div>{{ $document->valid_until?->format('d M Y') ?? '—' }}</div>
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
                            @if($document->tax_amount > 0)<tr><td class="text-muted">Tax ({{ rtrim(rtrim(number_format($document->tax_rate,2),'0'),'.') }}%)</td><td class="text-end ps-4">{{ money($document->tax_amount) }}</td></tr>@endif
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
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body small">
                        @if($document->notes)<p class="mb-2"><strong>Notes:</strong> {{ $document->notes }}</p>@endif
                        @if($document->terms)<p class="mb-0"><strong>Terms:</strong> {{ $document->terms }}</p>@endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            @if($document->isInvoice() && $document->balanceDue() > 0 && $document->status !== \App\Enums\DocumentStatus::Cancelled)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Record payment</h6></div>
                    <div class="card-body">
                        <form action="{{ route($routeBase . '.payment', $document) }}" method="POST">
                            @csrf
                            <label class="form-label small">Amount received ({{ currency_symbol() }})</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control mb-2" value="{{ from_kobo($document->balanceDue()) }}" required>
                            <button class="btn btn-success w-100 btn-sm">Record payment</button>
                        </form>
                    </div>
                </div>
            @endif

            @if($document->status !== \App\Enums\DocumentStatus::Cancelled && !$document->isPaid())
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-grid gap-2">
                        @unless($document->isEditable())
                            <form action="{{ route($routeBase . '.send', $document) }}" method="POST">@csrf
                                <button class="btn btn-outline-primary btn-sm w-100">Re-send to client</button>
                            </form>
                        @endunless
                        <form action="{{ route($routeBase . '.cancel', $document) }}" method="POST" onsubmit="return confirm('Cancel this {{ strtolower($type->label()) }}?');">@csrf
                            <button class="btn btn-outline-danger btn-sm w-100">Cancel</button>
                        </form>
                        @if($document->isEditable())
                            <form action="{{ route($routeBase . '.destroy', $document) }}" method="POST" onsubmit="return confirm('Delete this draft permanently?');">@csrf @method('DELETE')
                                <button class="btn btn-outline-dark btn-sm w-100">Delete draft</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function vlCopyShare(btn) {
    var input = document.getElementById('shareLink');
    input.select();
    input.setSelectionRange(0, 99999);
    var done = function () {
        var html = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied';
        setTimeout(function () { btn.innerHTML = html; }, 1800);
    };
    if (navigator.clipboard) {
        navigator.clipboard.writeText(input.value).then(done, function () { document.execCommand('copy'); done(); });
    } else {
        document.execCommand('copy'); done();
    }
}
</script>
@endpush
