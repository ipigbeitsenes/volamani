<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchingProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id', 'headline', 'bio', 'categories', 'skills',
        'min_budget', 'max_budget', 'serves_remote', 'locations',
        'is_accepting', 'leads_count', 'connections_count',
    ];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'skills' => 'array',
            'locations' => 'array',
            'min_budget' => 'integer',
            'max_budget' => 'integer',
            'serves_remote' => 'boolean',
            'is_accepting' => 'boolean',
            'leads_count' => 'integer',
            'connections_count' => 'integer',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
