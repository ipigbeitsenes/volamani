<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
