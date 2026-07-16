<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property DocumentType $type
 * @property string $number
 * @property string|null $public_token
 * @property int|null $vendor_id
 * @property string $issuer
 * @property int|null $client_id
 * @property string $client_name
 * @property string|null $client_email
 * @property string|null $client_phone
 * @property string|null $client_address
 * @property string|null $title
 * @property DocumentStatus $status
 * @property string $currency
 * @property int $subtotal
 * @property int $discount_amount
 * @property numeric $tax_rate
 * @property int $tax_amount
 * @property int $total
 * @property int $amount_paid
 * @property string|null $notes
 * @property string|null $terms
 * @property Carbon|null $issue_date
 * @property Carbon|null $due_date
 * @property Carbon|null $valid_until
 * @property int|null $converted_to_id
 * @property int|null $payment_id
 * @property int|null $created_by
 * @property Carbon|null $sent_at
 * @property Carbon|null $viewed_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $declined_at
 * @property string|null $signed_name
 * @property string|null $signed_ip
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $client
 * @property-read Document|null $convertedTo
 * @property-read User|null $creator
 * @property-read Collection<int, DocumentItem> $items
 * @property-read int|null $items_count
 * @property-read Payment|null $payment
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereAmountPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereClientAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereClientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereConvertedToId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDeclinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereIssuer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePublicToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSignedIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSignedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'number', 'public_token', 'vendor_id', 'issuer', 'client_id',
        'client_name', 'client_email', 'client_phone', 'client_address',
        'title', 'status', 'currency',
        'subtotal', 'discount_amount', 'tax_rate', 'tax_amount', 'total', 'amount_paid',
        'notes', 'terms', 'issue_date', 'due_date', 'valid_until',
        'converted_to_id', 'payment_id', 'created_by',
        'sent_at', 'viewed_at', 'paid_at', 'accepted_at', 'declined_at',
        'signed_name', 'signed_ip',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'status' => DocumentStatus::class,
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'amount_paid' => 'integer',
            'issue_date' => 'date',
            'due_date' => 'date',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'paid_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $document) {
            if (empty($document->public_token)) {
                $document->public_token = Str::random(40);
            }
        });
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

    /** Public, no-login share link the vendor sends to their client. */
    public function publicUrl(): string
    {
        return route('public.documents.show', $this->public_token);
    }

    public function isInvoice(): bool
    {
        return $this->type === DocumentType::Invoice;
    }

    public function isQuotation(): bool
    {
        return $this->type === DocumentType::Quotation;
    }

    public function isContract(): bool
    {
        return $this->type === DocumentType::Contract;
    }

    /** Issued by Volamani itself (no vendor) rather than by a seller. */
    public function isPlatformIssued(): bool
    {
        return $this->issuer === 'platform' || $this->vendor_id === null;
    }

    public function isSigned(): bool
    {
        return $this->status === DocumentStatus::Signed;
    }

    /** Display name of whoever issued this document (platform or vendor). */
    public function issuerName(): string
    {
        return $this->isPlatformIssued()
            ? config('app.name', 'Volamani')
            : ($this->vendor?->business_name ?? config('app.name', 'Volamani'));
    }

    /** Issuer logo URL or null (platform falls back to no logo). */
    public function issuerLogo(): ?string
    {
        if ($this->isPlatformIssued()) {
            return null;
        }

        return $this->vendor?->logo ? media_url($this->vendor->logo) : null;
    }

    /** Short issuer contact/location line for document headers. */
    public function issuerMeta(): ?string
    {
        if ($this->isPlatformIssued()) {
            return config('app.url');
        }

        $v = $this->vendor;
        if (! $v) {
            return null;
        }

        return collect([$v->city, $v->state])->filter()->join(', ') ?: ($v->whatsapp ?: null);
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
        $taxable = max(0, $subtotal - $this->discount_amount);
        $tax = (int) round($taxable * ((float) $this->tax_rate) / 100);

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total' => $taxable + $tax,
        ]);

        return $this;
    }
}
