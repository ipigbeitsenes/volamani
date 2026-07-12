<?php

namespace App\Models;

use App\Enums\ChatSenderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
