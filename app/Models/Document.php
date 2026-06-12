<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'number', 'vendor_id', 'client_id',
        'client_name', 'client_email', 'client_phone', 'client_address',
        'title', 'status', 'currency',
        'subtotal', 'discount_amount', 'tax_rate', 'tax_amount', 'total', 'amount_paid',
        'notes', 'terms', 'issue_date', 'due_date', 'valid_until',
        'converted_to_id', 'payment_id', 'created_by',
        'sent_at', 'viewed_at', 'paid_at', 'accepted_at', 'declined_at',
    ];

    protected function casts(): array
    {
        return [
            'type'            => DocumentType::class,
            'status'          => DocumentStatus::class,
            'subtotal'        => 'integer',
            'discount_amount' => 'integer',
            'tax_rate'        => 'decimal:2',
            'tax_amount'      => 'integer',
            'total'           => 'integer',
            'amount_paid'     => 'integer',
            'issue_date'      => 'date',
            'due_date'        => 'date',
            'valid_until'     => 'date',
            'sent_at'         => 'datetime',
            'viewed_at'       => 'datetime',
            'paid_at'         => 'datetime',
            'accepted_at'     => 'datetime',
            'declined_at'     => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DocumentItem::class)->orderBy('sort_order');
    }

    public function convertedTo(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'converted_to_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isInvoice(): bool
    {
        return $this->type === DocumentType::Invoice;
    }

    public function isQuotation(): bool
    {
        return $this->type === DocumentType::Quotation;
    }

    public function balanceDue(): int
    {
        return max(0, $this->total - $this->amount_paid);
    }

    public function isPaid(): bool
    {
        return $this->status === DocumentStatus::Paid;
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    public function isOverdue(): bool
    {
        return $this->isInvoice()
            && ! $this->isPaid()
            && $this->status !== DocumentStatus::Cancelled
            && $this->due_date !== null
            && $this->due_date->isPast()
            && $this->balanceDue() > 0;
    }

    /**
     * Recompute money columns from the line items + discount/tax. Persists and
     * returns the model. Call after items change.
     */
    public function recalcTotals(): self
    {
        $subtotal = (int) $this->items()->sum('amount');
        $taxable  = max(0, $subtotal - $this->discount_amount);
        $tax      = (int) round($taxable * ((float) $this->tax_rate) / 100);

        $this->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $tax,
            'total'      => $taxable + $tax,
        ]);

        return $this;
    }
}
