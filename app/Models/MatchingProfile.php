<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_id
 * @property string|null $headline
 * @property string|null $bio
 * @property array<array-key, mixed>|null $categories
 * @property array<array-key, mixed>|null $skills
 * @property int|null $min_budget
 * @property int|null $max_budget
 * @property bool $serves_remote
 * @property array<array-key, mixed>|null $locations
 * @property bool $is_accepting
 * @property int $leads_count
 * @property int $connections_count
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereConnectionsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereHeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereIsAccepting($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereLeadsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereLocations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereMaxBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereMinBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereServesRemote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereSkills($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchingProfile withoutTrashed()
 *
 * @mixin \Eloquent
 */
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
