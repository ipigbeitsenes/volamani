<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'is_system'   => 'boolean',
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
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }
}
