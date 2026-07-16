<?php

namespace App\Models;

use App\Enums\ChatConversationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $token
 * @property int|null $user_id
 * @property string|null $guest_name
 * @property string|null $guest_email
 * @property string|null $subject
 * @property ChatConversationStatus $status
 * @property int|null $assigned_to
 * @property Carbon|null $last_visitor_at
 * @property Carbon|null $last_agent_at
 * @property bool $bot_replied
 * @property int $agent_unread
 * @property int $visitor_unread
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $agent
 * @property-read ChatMessage|null $latestMessage
 * @property-read Collection<int, ChatMessage> $messages
 * @property-read int|null $messages_count
 * @property-read User|null $user
 *
 * @method static Builder<static>|ChatConversation newModelQuery()
 * @method static Builder<static>|ChatConversation newQuery()
 * @method static Builder<static>|ChatConversation open()
 * @method static Builder<static>|ChatConversation query()
 * @method static Builder<static>|ChatConversation whereAgentUnread($value)
 * @method static Builder<static>|ChatConversation whereAssignedTo($value)
 * @method static Builder<static>|ChatConversation whereBotReplied($value)
 * @method static Builder<static>|ChatConversation whereCreatedAt($value)
 * @method static Builder<static>|ChatConversation whereGuestEmail($value)
 * @method static Builder<static>|ChatConversation whereGuestName($value)
 * @method static Builder<static>|ChatConversation whereId($value)
 * @method static Builder<static>|ChatConversation whereLastAgentAt($value)
 * @method static Builder<static>|ChatConversation whereLastVisitorAt($value)
 * @method static Builder<static>|ChatConversation whereStatus($value)
 * @method static Builder<static>|ChatConversation whereSubject($value)
 * @method static Builder<static>|ChatConversation whereToken($value)
 * @method static Builder<static>|ChatConversation whereUpdatedAt($value)
 * @method static Builder<static>|ChatConversation whereUserId($value)
 * @method static Builder<static>|ChatConversation whereVisitorUnread($value)
 *
 * @mixin \Eloquent
 */
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

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
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
