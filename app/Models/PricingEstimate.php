<?php

namespace App\Models;

use App\Enums\PricingCategory;
use App\Enums\PricingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingEstimate extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'session_token', 'template_id',
        'category', 'service_name', 'pricing_type',
        'urgency', 'urgency_multiplier',
        'base_price', 'hourly_rate', 'estimated_hours',
        'add_ons', 'add_ons_total', 'milestones',
        'subtotal', 'total',
        'notes', 'client_name', 'client_email',
    ];

    protected function casts(): array
    {
        return [
            'category'           => PricingCategory::class,
            'pricing_type'       => PricingType::class,
            'add_ons'            => 'array',
            'milestones'         => 'array',
            'urgency_multiplier' => 'decimal:2',
            'estimated_hours'    => 'decimal:1',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->reference)) {
                $model->reference = generate_reference('PRC-');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PricingTemplate::class);
    }

    public function urgencyLabel(): string
    {
        return match ($this->urgency) {
            'soon'   => 'Soon (2 weeks)',
            'urgent' => 'Urgent (1 week)',
            'rush'   => 'Rush (48 hours)',
            default  => 'Normal timeline',
        };
    }

    public function urgencyBadge(): string
    {
        return match ($this->urgency) {
            'soon'   => 'warning',
            'urgent' => 'orange',
            'rush'   => 'danger',
            default  => 'secondary',
        };
    }
}
