@php
    $isInvoice  = $document->isInvoice();
    $isContract = $document->isContract();
    $overdue    = $document->isOverdue();
    $status     = $overdue ? \App\Enums\DocumentStatus::Overdue : $document->status;
    $payable    = $isInvoice
        && $document->balanceDue() > 0
        && $document->status !== \App\Enums\DocumentStatus::Cancelled;
    $signable   = $isContract
        && ! $document->isSigned()
        && in_array($document->status, [\App\Enums\DocumentStatus::Sent, \App\Enums\DocumentStatus::Viewed]);
    $issuerName = $document->issuerName();
    $logo       = $document->issuerLogo();
    $issuerMeta = $document->issuerMeta();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $document->type->label() }} {{ $document->number }} — {{ $document->issuerName() }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --vl-primary:#1a56db; --vl-ink:#0f172a; --vl-muted:#64748b; --vl-border:#e8ecf4;
            --vl-gradient:linear-gradient(135deg,#1a56db 0%,#4f46e5 100%);
            --vl-gradient-dark:linear-gradient(140deg,#0b1220 0%,#15275f 55%,#1a56db 120%);
        }
        *{-webkit-font-smoothing:antialiased;}
        body{font-family:'Inter',system-ui,sans-serif;background:#eef2fb;color:#334155;letter-spacing:-.01em;padding:0;margin:0;}
        h1,h2,h3,h4,h5,h6{font-family:'Plus Jakarta Sans','Inter',sans-serif;letter-spacing:-.025em;color:var(--vl-ink);}
        .doc-shell{max-width:860px;margin:0 auto;padding:2rem 1rem 4rem;}
        .doc-card{background:#fff;border:1px solid var(--vl-border);border-radius:20px;box-shadow:0 24px 50px -12px rgba(15,23,42,.18);overflow:hidden;}
        .doc-head{background:var(--vl-gradient-dark);color:#fff;padding:2rem;}
        .doc-logo{width:54px;height:54px;border-radius:14px;object-fit:cover;background:rgba(255,255,255,.12);}
        .doc-logo-fallback{width:54px;height:54px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.14);font-weight:800;font-size:1.4rem;color:#fff;}
        .doc-type{font-size:.72rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.7);}
        .amount-due{background:linear-gradient(135deg,rgba(26,86,219,.06),rgba(79,70,229,.06));border:1px solid var(--vl-border);border-radius:16px;}
        .table thead th{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--vl-muted);border-bottom:2px solid var(--vl-border);}
        .btn{font-weight:600;border-radius:12px;}
        .btn-primary{background:var(--vl-gradient);border:none;box-shadow:0 10px 22px -8px rgba(26,86,219,.6);}
        .btn-primary:hover{transform:translateY(-2px);}
        .btn-lg{padding:.85rem 1.6rem;}
        .pay-bar{position:sticky;bottom:0;z-index:5;}
        .text-muted{color:var(--vl-muted)!important;}
        .badge-status{font-weight:600;border-radius:8px;padding:.4em .7em;}
        .powered{color:#94a3b8;font-size:.8rem;}
        .powered a{color:var(--vl-primary);text-decoration:none;font-weight:600;}
    </style>
</head>
<body>
<div class="doc-shell">

    {{-- flash --}}
    @foreach (['success'=>'success','error'=>'danger','info'=>'info','warning'=>'warning'] as $key=>$variant)
        @if(session($key))
            <div class="alert alert-{{ $variant }} alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    <div class="doc-card">
        {{-- Header --}}
        <div class="doc-head">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($logo)
                        <img src="{{ $logo }}" alt="{{ $issuerName }}" class="doc-logo">
                    @else
                        <div class="doc-logo-fallback">{{ strtoupper(substr($issuerName,0,1)) }}</div>
                    @endif
                    <div>
                        <div class="fw-bold fs-5 text-white">{{ $issuerName }}</div>
                        @if($issuerMeta)
                            <div class="small" style="color:rgba(255,255,255,.65)">{{ $issuerMeta }}</div>
                        @endif
                        @if($document->isPlatformIssued())
                            <div class="small" style="color:rgba(255,255,255,.55)">Official platform document</div>
                        @endif
                    </div>
                </div>
                <div class="text-md-end">
                    <div class="doc-type">{{ $document->type->label() }}</div>
                    <div class="fw-bold fs-4 text-white">{{ $document->number }}</div>
                    <span class="badge badge-status bg-{{ $status->badge() }}">{{ $status->label() }}</span>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-4 p-md-5">

            {{-- Amount headline --}}
            @if($isInvoice)
                <div class="amount-due p-3 p-md-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="text-muted small">{{ $document->isPaid() ? 'Amount paid' : 'Amount due' }}</div>
                        <div class="fw-bold fs-2">
                            {{ money($document->isPaid() ? $document->total : $document->balanceDue()) }}
                        </div>
                        @if($document->due_date && !$document->isPaid())
                            <div class="small {{ $overdue ? 'text-danger' : 'text-muted' }}">
                                <i class="bi bi-calendar-event me-1"></i>Due {{ $document->due_date->format('d M Y') }}
                            </div>
                        @endif
                    </div>
                    @if($document->isPaid())
                        <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>Paid in full</span>
                    @endif
                </div>
            @endif

            {{-- Parties --}}
            <div class="row mb-4">
                <div class="col-6">
                    <div class="text-muted small text-uppercase">Billed to</div>
                    <div class="fw-semibold">{{ $document->client_name }}</div>
                    @if($document->client_email)<div class="small">{{ $document->client_email }}</div>@endif
                    @if($document->client_phone)<div class="small text-muted">{{ $document->client_phone }}</div>@endif
                    @if($document->client_address)<div class="small text-muted">{{ $document->client_address }}</div>@endif
                </div>
                <div class="col-6 text-end">
                    <div class="text-muted small text-uppercase">Issued</div>
                    <div>{{ $document->issue_date?->format('d M Y') ?? '—' }}</div>
                    @if(!$isInvoice && $document->valid_until)
                        <div class="text-muted small text-uppercase mt-2">Valid until</div>
                        <div>{{ $document->valid_until->format('d M Y') }}</div>
                    @endif
                    @if($document->title)<div class="small text-muted mt-2">{{ $document->title }}</div>@endif
                </div>
            </div>

            {{-- Line items --}}
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
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
            </div>

            <div class="d-flex justify-content-end">
                <table class="table table-sm w-auto mb-0">
                    <tr><td class="text-muted pe-4">Subtotal</td><td class="text-end">{{ money($document->subtotal) }}</td></tr>
                    @if($document->discount_amount > 0)<tr><td class="text-muted pe-4">Discount</td><td class="text-end">−{{ money($document->discount_amount) }}</td></tr>@endif
                    @if($document->tax_amount > 0)<tr><td class="text-muted pe-4">Tax ({{ rtrim(rtrim(number_format($document->tax_rate,2),'0'),'.') }}%)</td><td class="text-end">{{ money($document->tax_amount) }}</td></tr>@endif
                    <tr class="fw-bold border-top"><td class="pe-4">Total</td><td class="text-end">{{ money($document->total) }}</td></tr>
                    @if($isInvoice && $document->amount_paid > 0)
                        <tr><td class="text-muted pe-4">Paid</td><td class="text-end text-success">−{{ money($document->amount_paid) }}</td></tr>
                        <tr class="fw-bold"><td class="pe-4">Balance due</td><td class="text-end">{{ money($document->balanceDue()) }}</td></tr>
                    @endif
                </table>
            </div>

            @if($document->notes || $document->terms)
                <hr class="my-4">
                <div class="small text-muted">
                    @if($document->notes)<p class="mb-2"><strong class="text-dark">Notes:</strong> {{ $document->notes }}</p>@endif
                    @if($document->terms)<p class="mb-0"><strong class="text-dark">Terms:</strong> {{ $document->terms }}</p>@endif
                </div>
            @endif

            {{-- Contract: already signed --}}
            @if($isContract && $document->isSigned())
                <div class="alert alert-success d-flex align-items-center gap-2 border-0 mt-4">
                    <i class="bi bi-patch-check-fill fs-5"></i>
                    <div>Signed by <strong>{{ $document->signed_name }}</strong> on {{ $document->accepted_at?->format('d M Y, H:i') }}.</div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="d-flex flex-wrap gap-2 mt-4">
                <a href="{{ route('public.documents.print', $document->public_token) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>Print / PDF
                </a>

                @if($document->isQuotation() && in_array($document->status, [\App\Enums\DocumentStatus::Sent, \App\Enums\DocumentStatus::Viewed]))
                    <form action="{{ route('public.documents.accept', $document->public_token) }}" method="POST">
                        @csrf<button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Accept quote</button>
                    </form>
                    <form action="{{ route('public.documents.decline', $document->public_token) }}" method="POST"
                          onsubmit="return confirm('Decline this quotation?');">
                        @csrf<button class="btn btn-outline-danger">Decline</button>
                    </form>
                @endif
            </div>

            {{-- Contract: e-signature block --}}
            @if($signable)
                <div class="mt-4 p-3 p-md-4 rounded-3" style="background:rgba(26,86,219,.05);border:1px solid var(--vl-border);">
                    <h6 class="fw-bold mb-1"><i class="bi bi-pen me-1 text-primary"></i>Sign this contract</h6>
                    <p class="text-muted small mb-3">By typing your full name and agreeing below, you accept the terms of this contract of sale. Your name, the date and your IP address will be recorded.</p>
                    <form action="{{ route('public.documents.sign', $document->public_token) }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-sm-7">
                            <label class="form-label small fw-semibold">Full legal name</label>
                            <input type="text" name="signed_name" class="form-control @error('signed_name') is-invalid @enderror" maxlength="120" required>
                        </div>
                        <div class="col-sm-5">
                            <button class="btn btn-primary w-100"><i class="bi bi-check2-circle me-1"></i>Agree &amp; Sign</button>
                        </div>
                        <div class="col-12">
                            <div class="form-check small">
                                <input class="form-check-input @error('agree') is-invalid @enderror" type="checkbox" name="agree" value="1" id="agree" required>
                                <label class="form-check-label text-muted" for="agree">I have read and agree to the terms of this contract.</label>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Sticky pay bar (invoices with a balance) --}}
    @if($payable)
        <div class="pay-bar mt-3">
            <div class="doc-card p-3 d-flex justify-content-between align-items-center flex-wrap gap-3"
                 style="border-radius:16px;box-shadow:0 12px 30px -10px rgba(15,23,42,.25);">
                <div>
                    <div class="text-muted small">Amount due</div>
                    <div class="fw-bold fs-4">{{ money($document->balanceDue()) }}</div>
                </div>
                <form action="{{ route('public.documents.pay', $document->public_token) }}" method="POST" class="m-0">
                    @csrf
                    <button class="btn btn-primary btn-lg">
                        <i class="bi bi-shield-lock me-2"></i>Pay securely now
                    </button>
                </form>
            </div>
            <p class="text-center powered mt-3 mb-0">
                <i class="bi bi-lock-fill me-1"></i>Secured by Paystack ·
                Powered by <a href="{{ route('home') }}">Volamani</a>
            </p>
        </div>
    @else
        <p class="text-center powered mt-4 mb-0">Powered by <a href="{{ route('home') }}">Volamani</a></p>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
