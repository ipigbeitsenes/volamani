<?php

namespace App\Models;

use App\Enums\InteractionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property int|null $user_id
 * @property InteractionType $type
 * @property string|null $title
 * @property string|null $body
 * @property bool $pinned
 * @property Carbon|null $due_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $occurred_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $author
 * @property-read Client|null $client
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereOccurredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction wherePinned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientInteraction whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ClientInteraction extends Model
{
    protected $fillable = [
        'client_id', 'user_id', 'type', 'title', 'body',
        'pinned', 'due_at', 'completed_at', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => InteractionType::class,
            'pinned' => 'boolean',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'occurred_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isTask(): bool
    {
        return $this->type === InteractionType::Task;
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->isTask()
            && ! $this->isComplete()
            && $this->due_at !== null
            && $this->due_at->isPast();
    }
}
