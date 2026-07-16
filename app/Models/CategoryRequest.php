<?php

namespace App\Models;

use App\Enums\CategoryDomain;
use App\Enums\CategoryRequestStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $vendor_id
 * @property CategoryDomain $domain
 * @property string $name
 * @property int|null $parent_id
 * @property string|null $reason
 * @property CategoryRequestStatus $status
 * @property string|null $admin_note
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property int|null $created_category_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $reviewedBy
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereAdminNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereCreatedCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryRequest whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class CategoryRequest extends Model
{
    use Auditable;

    protected $fillable = [
        'vendor_id', 'domain', 'name', 'parent_id', 'reason',
        'status', 'admin_note', 'reviewed_by', 'reviewed_at', 'created_category_id',
    ];

    protected function casts(): array
    {
        return [
            'domain' => CategoryDomain::class,
            'status' => CategoryRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === CategoryRequestStatus::Pending;
    }

    public function scopePending($query)
    {
        return $query->where('status', CategoryRequestStatus::Pending->value);
    }
}
