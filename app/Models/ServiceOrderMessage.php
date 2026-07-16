<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $service_order_id
 * @property int $sender_id
 * @property string|null $message
 * @property string|null $attachment
 * @property string|null $attachment_name
 * @property bool $is_delivery
 * @property bool $is_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $attachment_url
 * @property-read User|null $sender
 * @property-read ServiceOrder|null $serviceOrder
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereAttachmentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereIsDelivery($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereServiceOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrderMessage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ServiceOrderMessage extends Model
{
    protected $fillable = [
        'service_order_id', 'sender_id', 'message',
        'attachment', 'attachment_name', 'is_delivery', 'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_delivery' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return media_url($this->attachment);
    }
}
