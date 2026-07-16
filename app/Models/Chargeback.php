<?php

namespace App\Models;

use App\Enums\ChargebackStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $reference
 * @property int|null $payment_id
 * @property int|null $escrow_id
 * @property int|null $buyer_id
 * @property int|null $vendor_id
 * @property string|null $gateway_reference
 * @property int $amount
 * @property int $clawed_back_amount
 * @property int $unrecovered_amount
 * @property string|null $reason
 * @property ChargebackStatus $status
 * @property array<array-key, mixed>|null $evidence
 * @property int|null $resolved_by
 * @property string|null $resolution_note
 * @property Carbon|null $resolved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $buyer
 * @property-read Escrow|null $escrow
 * @property-read Payment|null $payment
 * @property-read User|null $resolvedBy
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereClawedBackAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereEscrowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereEvidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereGatewayReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereResolutionNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereResolvedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereUnrecoveredAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chargeback withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Chargeback extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'reference', 'payment_id', 'escrow_id', 'buyer_id', 'vendor_id',
        'gateway_reference', 'amount', 'clawed_back_amount', 'unrecovered_amount',
        'reason', 'status', 'evidence',
        'resolved_by', 'resolution_note', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChargebackStatus::class,
            'evidence' => 'array',
            'amount' => 'integer',
            'clawed_back_amount' => 'integer',
            'unrecovered_amount' => 'integer',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Chargeback $c) {
            if (empty($c->reference)) {
                $c->reference = generate_reference('CBK');
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // ─── State helpers ──────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    public function canContest(): bool
    {
        return $this->status === ChargebackStatus::Open;
    }

    public function canResolve(): bool
    {
        return $this->status->isOpen();
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [ChargebackStatus::Open->value, ChargebackStatus::Contested->value]);
    }

    public function evidenceNote(): ?string
    {
        return $this->evidence['note'] ?? null;
    }

    public function evidenceFiles(): array
    {
        return $this->evidence['files'] ?? [];
    }
}
