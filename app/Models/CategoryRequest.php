<?php

namespace App\Models;

use App\Enums\CategoryDomain;
use App\Enums\CategoryRequestStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'domain'      => CategoryDomain::class,
            'status'      => CategoryRequestStatus::class,
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
