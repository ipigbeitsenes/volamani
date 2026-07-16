<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $seller_conversation_id
 * @property int $sender_id
 * @property string $body
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SellerConversation $conversation
 * @property-read User|null $sender
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage whereSellerConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerMessage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SellerMessage extends Model
{
    protected $fillable = ['seller_conversation_id', 'sender_id', 'body', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SellerConversation::class, 'seller_conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
