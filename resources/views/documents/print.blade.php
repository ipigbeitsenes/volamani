<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; margin: 0; padding: 40px; font-size: 14px; }
        .wrap { max-width: 800px; margin: 0 auto; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
        .doc-title { font-size: 28px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #1a56db; margin: 0; }
        .muted { color: #64748b; }
        .vendor-name { font-size: 18px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        th { text-align: left; border-bottom: 2px solid #1e293b; padding: 8px; font-size: 12px; text-transform: uppercase; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .right { text-align: right; }
        .totals { width: 280px; margin-left: auto; }
        .totals td { border: none; padding: 4px 8px; }
        .grand { font-weight: 800; font-size: 16px; border-top: 2px solid #1e293b; }
        .meta { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 16px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; background: #e2e8f0; font-size: 12px; font-weight: 600; }
        .notes { margin-top: 32px; font-size: 13px; color: #475569; }
        .print-btn { margin-bottom: 20px; }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="wrap">
        <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

        <div class="head">
            <div>
                <div class="vendor-name">{{ $document->vendor->business_name }}</div>
                @if($document->vendor->city)<div class="muted">{{ $document->vendor->city }}{{ $document->vendor->state ? ', ' . $document->vendor->state : '' }}</div>@endif
                @if($document->vendor->whatsapp)<div class="muted">{{ $document->vendor->whatsapp }}</div>@endif
            </div>
            <div class="right">
                <h1 class="doc-title">{{ $document->type->label() }}</h1>
                <div class="muted">{{ $document->number }}</div>
                <div><span class="badge">{{ $document->status->label() }}</span></div>
            </div>
        </div>

        <div class="meta">
            <div>
                <div class="muted">Billed to</div>
                <strong>{{ $document->client_name }}</strong>
                @if($document->client_email)<div>{{ $document->client_email }}</div>@endif
                @if($document->client_phone)<div>{{ $document->client_phone }}</div>@endif
                @if($document->client_address)<div class="muted">{{ $document->client_address }}</div>@endif
            </div>
            <div class="right">
                <div><span class="muted">Issued:</span> {{ $document->issue_date?->format('d M Y') ?? '—' }}</div>
                @if($document->isInvoice())
                    <div><span class="muted">Due:</span> {{ $document->due_date?->format('d M Y') ?? '—' }}</div>
                @else
                    <div><span class="muted">Valid until:</span> {{ $document->valid_until?->format('d M Y') ?? '—' }}</div>
                @endif
                @if($document->title)<div class="muted">{{ $document->title }}</div>@endif
            </div>
        </div>

        <table>
            <thead>
                <tr><th>Description</th><th class="right">Qty</th><th class="right">Unit Price</th><th class="right">Amount</th></tr>
            </thead>
            <tbody>
                @foreach($document->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td class="right">{{ money($item->unit_price) }}</td>
                        <td class="right">{{ money($item->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr><td class="muted">Subtotal</td><td class="right">{{ money($document->subtotal) }}</td></tr>
            @if($document->discount_amount > 0)<tr><td class="muted">Discount</td><td class="right">−{{ money($document->discount_amount) }}</td></tr>@endif
            @if($document->tax_amount > 0)<tr><td class="muted">Tax ({{ rtrim(rtrim(number_format($document->tax_rate,2),'0'),'.') }}%)</td><td class="right">{{ money($document->tax_amount) }}</td></tr>@endif
            <tr class="grand"><td>Total</td><td class="right">{{ money($document->total) }}</td></tr>
            @if($document->isInvoice() && $document->amount_paid > 0)
                <tr><td class="muted">Paid</td><td class="right">−{{ money($document->amount_paid) }}</td></tr>
                <tr class="grand"><td>Balance Due</td><td class="right">{{ money($document->balanceDue()) }}</td></tr>
            @endif
        </table>

        @if($document->notes || $document->terms)
            <div class="notes">
                @if($document->notes)<p><strong>Notes:</strong> {{ $document->notes }}</p>@endif
                @if($document->terms)<p><strong>Terms &amp; Conditions:</strong> {{ $document->terms }}</p>@endif
            </div>
        @endif
    </div>
</body>
</html>
