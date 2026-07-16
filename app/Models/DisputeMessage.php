<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $dispute_id
 * @property int|null $sender_id
 * @property string|null $message
 * @property string|null $attachment
 * @property string|null $attachment_name
 * @property bool $is_staff
 * @property bool $is_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Dispute|null $dispute
 * @property-read User|null $sender
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereAttachmentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereDisputeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereIsStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DisputeMessage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class DisputeMessage extends Model
{
    protected $fillable = [
        'dispute_id', 'sender_id', 'message',
        'attachment', 'attachment_name', 'is_staff', 'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_staff' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachmentUrl(): ?string
    {
        return $this->attachment ? Storage::disk('public')->url($this->attachment) : null;
    }
}
