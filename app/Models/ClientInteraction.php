<?php

namespace App\Models;

use App\Enums\InteractionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
