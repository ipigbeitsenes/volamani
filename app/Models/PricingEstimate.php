<?php

namespace App\Models;

use App\Enums\PricingCategory;
use App\Enums\PricingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int|null $user_id
 * @property string|null $session_token
 * @property int|null $template_id
 * @property PricingCategory $category
 * @property string $service_name
 * @property PricingType $pricing_type
 * @property string $urgency
 * @property numeric $urgency_multiplier
 * @property int $base_price
 * @property int $hourly_rate
 * @property numeric $estimated_hours
 * @property array<array-key, mixed>|null $add_ons
 * @property int $add_ons_total
 * @property array<array-key, mixed>|null $milestones
 * @property int $subtotal
 * @property int $total
 * @property string|null $notes
 * @property string|null $client_name
 * @property string|null $client_email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PricingTemplate|null $template
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereAddOns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereAddOnsTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereEstimatedHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereMilestones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate wherePricingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereSessionToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereUrgency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereUrgencyMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingEstimate whereUserId($value)
 *
 * @mixin \Eloquent
 */
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
            'category' => PricingCategory::class,
            'pricing_type' => PricingType::class,
            'add_ons' => 'array',
            'milestones' => 'array',
            'urgency_multiplier' => 'decimal:2',
            'estimated_hours' => 'decimal:1',
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
            'soon' => 'Soon (2 weeks)',
            'urgent' => 'Urgent (1 week)',
            'rush' => 'Rush (48 hours)',
            default => 'Normal timeline',
        };
    }

    public function urgencyBadge(): string
    {
        return match ($this->urgency) {
            'soon' => 'warning',
            'urgent' => 'orange',
            'rush' => 'danger',
            default => 'secondary',
        };
    }
}
