<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $service_id
 * @property string $question
 * @property string $answer
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read FreelanceService|null $service
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFaq whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ServiceFaq extends Model
{
    protected $fillable = ['service_id', 'question', 'answer', 'sort_order'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(FreelanceService::class, 'service_id');
    }
}
