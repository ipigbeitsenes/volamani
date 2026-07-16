<?php

namespace App\Models;

use App\Enums\ConsultationSessionStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int $package_id
 * @property int $profile_id
 * @property int $buyer_id
 * @property ConsultationSessionStatus $status
 * @property PaymentStatus $payment_status
 * @property int $price
 * @property int $platform_fee
 * @property int $consultant_earnings
 * @property string|null $payment_reference
 * @property Carbon|null $scheduled_at
 * @property int $duration_minutes
 * @property string|null $meeting_link
 * @property string|null $meeting_platform
 * @property string|null $notes
 * @property string|null $consultant_notes
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $buyer
 * @property-read ConsultationPackage $package
 * @property-read ConsultantProfile $profile
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereConsultantEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereConsultantNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereMeetingLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereMeetingPlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession wherePaymentReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession wherePlatformFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationSession withoutTrashed()
 *
 * @mixin \Eloquent
 */
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
