@extends('layouts.vendor')

@section('title', ($document->exists ? 'Edit ' : 'New ') . $type->label())

@section('content')
<div class="container-fluid py-4" style="max-width: 960px;">
    <h4 class="fw-bold mb-4">{{ $document->exists ? 'Edit ' . $document->number : 'New ' . $type->label() }}</h4>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    @php
        $oldItems = old('items', $document->exists
            ? $document->items->map(fn($i) => ['description' => $i->description, 'quantity' => $i->quantity, 'unit_price' => from_kobo($i->unit_price)])->all()
            : [['description' => '', 'quantity' => 1, 'unit_price' => '']]);
    @endphp

    <form method="POST" action="{{ $document->exists ? route($routeBase . '.update', $document) : route($routeBase . '.store') }}">
        @csrf
        @if($document->exists) @method('PUT') @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0">Client</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client name</label>
                        <input type="text" name="client_name" class="form-control" value="{{ old('client_name', $document->client_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Client email</label>
                        <input type="email" name="client_email" class="form-control" value="{{ old('client_email', $document->client_email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Client phone</label>
                        <input type="text" name="client_phone" class="form-control" value="{{ old('client_phone', $document->client_phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Linked account user ID <span class="text-muted small">(optional)</span></label>
                        <input type="number" name="client_id" class="form-control" value="{{ old('client_id', $document->client_id) }}" placeholder="If the client has a Volamani account">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Client address</label>
                        <textarea name="client_address" class="form-control" rows="2">{{ old('client_address', $document->client_address) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0">Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title <span class="text-muted small">(optional)</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $document->title) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Issue date</label>
                        <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', optional($document->issue_date)->toDateString() ?? now()->toDateString()) }}">
                    </div>
                    @if($type->isInvoice())
                        <div class="col-md-3">
                            <label class="form-label">Due date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', optional($document->due_date)->toDateString()) }}">
                        </div>
                    @else
                        <div class="col-md-3">
                            <label class="form-label">Valid until</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', optional($document->valid_until)->toDateString()) }}">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Line items</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()"><i class="bi bi-plus"></i> Add item</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr><th style="width:45%">Description</th><th>Qty</th><th>Unit price (₦)</th><th class="text-end">Amount</th><th></th></tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Subtotal</td>
                                <td class="text-end fw-semibold" id="subtotalCell">₦0.00</td><td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Discount (₦)</label>
                        <input type="number" step="0.01" min="0" name="discount" id="discount" class="form-control" value="{{ old('discount', $document->exists ? from_kobo($document->discount_amount) : 0) }}" oninput="recalc()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tax rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" id="tax_rate" class="form-control" value="{{ old('tax_rate', $document->tax_rate ?? 0) }}" oninput="recalc()">
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light">
                            <div class="d-flex justify-content-between small"><span>Subtotal</span><span id="sumSubtotal">₦0.00</span></div>
                            <div class="d-flex justify-content-between small"><span>Discount</span><span id="sumDiscount">₦0.00</span></div>
                            <div class="d-flex justify-content-between small"><span>Tax</span><span id="sumTax">₦0.00</span></div>
                            <hr class="my-1">
                            <div class="d-flex justify-content-between fw-bold"><span>Total</span><span id="sumTotal">₦0.00</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $document->notes) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Terms</label>
                        <textarea name="terms" class="form-control" rows="2">{{ old('terms', $document->terms) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">{{ $document->exists ? 'Save changes' : 'Create ' . $type->label() }}</button>
            <a href="{{ route($routeBase . '.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let itemIndex = 0;
    const initialItems = @json(array_values($oldItems));

    function fmt(n) { return '₦' + (n || 0).toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

    function addItem(item = {description: '', quantity: 1, unit_price: ''}) {
        const i = itemIndex++;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="items[${i}][description]" class="form-control form-control-sm" value="${item.description ?? ''}" required></td>
            <td><input type="number" step="0.01" min="0.01" name="items[${i}][quantity]" class="form-control form-control-sm item-qty" style="width:90px" value="${item.quantity ?? 1}" oninput="recalc()" required></td>
            <td><input type="number" step="0.01" min="0" name="items[${i}][unit_price]" class="form-control form-control-sm item-price" style="width:130px" value="${item.unit_price ?? ''}" oninput="recalc()" required></td>
            <td class="text-end item-amount">₦0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalc();"><i class="bi bi-x"></i></button></td>`;
        document.getElementById('itemsBody').appendChild(row);
        recalc();
    }

    function recalc() {
        let subtotal = 0;
        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            const amount = qty * price;
            subtotal += amount;
            row.querySelector('.item-amount').textContent = fmt(amount);
        });
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
        const taxable = Math.max(0, subtotal - discount);
        const tax = taxable * taxRate / 100;
        const total = taxable + tax;

        document.getElementById('subtotalCell').textContent = fmt(subtotal);
        document.getElementById('sumSubtotal').textContent = fmt(subtotal);
        document.getElementById('sumDiscount').textContent = fmt(discount);
        document.getElementById('sumTax').textContent = fmt(tax);
        document.getElementById('sumTotal').textContent = fmt(total);
    }

    (initialItems.length ? initialItems : [{description:'',quantity:1,unit_price:''}]).forEach(addItem);
</script>
@endpush
