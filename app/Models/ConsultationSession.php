<?php

namespace App\Models;

use App\Enums\ConsultationSessionStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultationSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'package_id', 'profile_id', 'buyer_id',
        'status', 'payment_status', 'price', 'platform_fee',
        'consultant_earnings', 'payment_reference',
        'scheduled_at', 'duration_minutes', 'meeting_link', 'meeting_platform',
        'notes', 'consultant_notes',
        'confirmed_at', 'started_at', 'completed_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ConsultationSessionStatus::class,
            'payment_status' => PaymentStatus::class,
            'scheduled_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ConsultationSession $session) {
            if (empty($session->reference)) {
                $session->reference = generate_reference('CON');
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function package(): BelongsTo
    {
        return $this->belongsTo(ConsultationPackage::class, 'package_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ConsultantProfile::class, 'profile_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Success;
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === ConsultationSessionStatus::Pending && $this->isPaid();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            ConsultationSessionStatus::Pending,
            ConsultationSessionStatus::Confirmed,
        ]);
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status, [
            ConsultationSessionStatus::Confirmed,
            ConsultationSessionStatus::InProgress,
        ]);
    }

    public function isUpcoming(): bool
    {
        return $this->status === ConsultationSessionStatus::Confirmed
            && $this->scheduled_at
            && $this->scheduled_at->isFuture();
    }
}
