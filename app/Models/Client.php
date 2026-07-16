<?php

namespace App\Models;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $vendor_id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $company
 * @property string|null $address
 * @property ClientStatus $status
 * @property ClientSource $source
 * @property array<array-key, mixed>|null $tags
 * @property string|null $about
 * @property int $total_spent
 * @property int $orders_count
 * @property Carbon|null $last_interaction_at
 * @property Carbon|null $last_synced_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ClientInteraction> $interactions
 * @property-read int|null $interactions_count
 * @property-read User|null $user
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereLastInteractionAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereLastSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereOrdersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTotalSpent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id', 'user_id', 'name', 'email', 'phone', 'company', 'address',
        'status', 'source', 'tags', 'about',
        'total_spent', 'orders_count', 'last_interaction_at', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
            'source' => ClientSource::class,
            'tags' => 'array',
            'total_spent' => 'integer',
            'orders_count' => 'integer',
            'last_interaction_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ClientInteraction::class)
            ->orderByDesc('pinned')
            ->orderByDesc('created_at');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($p) => Str::upper(Str::substr($p, 0, 1)))
            ->implode('');
    }

    public function isRegistered(): bool
    {
        return $this->user_id !== null;
    }
}
