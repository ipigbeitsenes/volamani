<?php

namespace App\Models;

use App\Enums\ChatConversationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatConversation extends Model
{
    protected $fillable = [
        'token', 'user_id', 'guest_name', 'guest_email', 'subject',
        'status', 'assigned_to', 'last_visitor_at', 'last_agent_at',
        'bot_replied', 'agent_unread', 'visitor_unread',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChatConversationStatus::class,
            'last_visitor_at' => 'datetime',
            'last_agent_at' => 'datetime',
            'bot_replied' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $conversation) {
            $conversation->token ??= (string) Str::uuid();
            // Set defaults on the instance itself — DB column defaults are NOT
            // reflected back on the model returned by create(), which otherwise
            // leaves status null and crashes status->value on the first response.
            $conversation->status ??= ChatConversationStatus::Open;
            $conversation->bot_replied ??= false;
            $conversation->agent_unread ??= 0;
            $conversation->visitor_unread ??= 0;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->latestOfMany();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ChatConversationStatus::Open->value,
            ChatConversationStatus::Pending->value,
        ]);
    }

    public function isClosed(): bool
    {
        return $this->status === ChatConversationStatus::Closed;
    }

    /** Best available display name for the person on the visitor side. */
    public function visitorName(): string
    {
        return $this->user?->name
            ?? ($this->guest_name ?: 'Guest visitor');
    }

    public function visitorEmail(): ?string
    {
        return $this->user?->email ?? $this->guest_email;
    }
}
