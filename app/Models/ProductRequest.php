<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $buyer_id
 * @property int|null $vendor_id
 * @property int|null $category_id
 * @property string $title
 * @property string $description
 * @property int|null $budget_min
 * @property int|null $budget_max
 * @property array<array-key, mixed>|null $attachments
 * @property Carbon|null $deadline_at
 * @property RequestStatus $status
 * @property-read int|null $quotations_count
 * @property int|null $accepted_quotation_id
 * @property Carbon|null $closed_at
 * @property bool $is_public
 * @property string|null $location
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductRequestQuotation|null $acceptedQuotation
 * @property-read User|null $buyer
 * @property-read ProductCategory|null $category
 * @property-read Collection<int, ProductRequestQuotation> $quotations
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest forBuyer(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest forVendor(int $vendorId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereAcceptedQuotationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereBudgetMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereBudgetMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereDeadlineAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereQuotationsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequest withoutTrashed()
 *
 * @mixin \Eloquent
 */
class ProductRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'buyer_id', 'vendor_id', 'category_id', 'title', 'description',
        'budget_min', 'budget_max', 'attachments', 'deadline_at',
        'status', 'quotations_count', 'accepted_quotation_id',
        'closed_at', 'is_public', 'location',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'attachments' => 'array',
            'is_public' => 'boolean',
            'deadline_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /** The target vendor when this is a direct request (null = open board). */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(ProductRequestQuotation::class, 'request_id');
    }

    public function acceptedQuotation(): BelongsTo
    {
        return $this->belongsTo(ProductRequestQuotation::class, 'accepted_quotation_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', RequestStatus::Open)->where('is_public', true);
    }

    public function scopeForBuyer($query, int $userId)
    {
        return $query->where('buyer_id', $userId);
    }

    /** Direct requests sent to a specific vendor. */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function isDirect(): bool
    {
        return $this->vendor_id !== null;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === RequestStatus::Open;
    }

    public function isExpired(): bool
    {
        return $this->deadline_at && $this->deadline_at->isPast() && $this->isOpen();
    }

    public function budgetRange(): string
    {
        if ($this->budget_min && $this->budget_max) {
            return money($this->budget_min).' – '.money($this->budget_max);
        }
        if ($this->budget_max) {
            return 'Up to '.money($this->budget_max);
        }
        if ($this->budget_min) {
            return 'From '.money($this->budget_min);
        }

        return 'Flexible';
    }

    public function hasQuotedBy(Vendor $vendor): bool
    {
        return $this->quotations()->where('vendor_id', $vendor->id)->exists();
    }

    public function getQuotationBy(Vendor $vendor): ?ProductRequestQuotation
    {
        return $this->quotations()->where('vendor_id', $vendor->id)->first();
    }
}
