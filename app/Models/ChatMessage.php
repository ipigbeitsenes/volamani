<?php

namespace App\Models;

use App\Enums\ChatSenderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $chat_conversation_id
 * @property int|null $user_id
 * @property ChatSenderType $sender_type
 * @property string $body
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ChatConversation $conversation
 * @property-read User|null $sender
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereChatConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereSenderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ChatMessage extends Model
{
    protected $fillable = [
        'chat_conversation_id', 'user_id', 'sender_type', 'body', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'sender_type' => ChatSenderType::class,
            'read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isFromVisitor(): bool
    {
        return $this->sender_type === ChatSenderType::Visitor;
    }

    public function isFromTeam(): bool
    {
        return in_array($this->sender_type, [ChatSenderType::Agent, ChatSenderType::Bot, ChatSenderType::System], true);
    }
}
