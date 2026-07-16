<?php

namespace App\Models;

use App\Enums\DisputeReason;
use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $reference
 * @property int $escrow_id
 * @property int $buyer_id
 * @property int $vendor_id
 * @property int $raised_by
 * @property DisputeReason $reason
 * @property string $description
 * @property DisputeStatus $status
 * @property DisputeResolution|null $resolution
 * @property int|null $resolution_amount
 * @property string|null $resolution_note
 * @property int|null $resolved_by
 * @property Carbon|null $resolved_at
 * @property Carbon|null $escalated_at
 * @property Carbon|null $response_due_at
 * @property bool $sla_breached
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $buyer
 * @property-read Escrow $escrow
 * @property-read Collection<int, DisputeMessage> $messages
 * @property-read int|null $messages_count
 * @property-read User|null $raisedBy
 * @property-read User|null $resolvedBy
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereEscalatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereEscrowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereRaisedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereResolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereResolutionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereResolutionNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereResolvedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereResponseDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereSlaBreached($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dispute withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Dispute extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'reference', 'escrow_id', 'buyer_id', 'vendor_id', 'raised_by',
        'reason', 'description', 'status',
        'resolution', 'resolution_amount', 'resolution_note', 'resolved_by',
        'resolved_at', 'escalated_at', 'response_due_at', 'sla_breached',
    ];

    protected function casts(): array
    {
        return [
            'reason' => DisputeReason::class,
            'status' => DisputeStatus::class,
            'resolution' => DisputeResolution::class,
            'resolution_amount' => 'integer',
            'resolved_at' => 'datetime',
            'escalated_at' => 'datetime',
            'response_due_at' => 'datetime',
            'sla_breached' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Dispute $dispute) {
            if (empty($dispute->reference)) {
                $dispute->reference = generate_reference('DSP');
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

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

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class)->orderBy('created_at');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return in_array($this->status, [
            DisputeStatus::Open,
            DisputeStatus::UnderReview,
            DisputeStatus::AwaitingResponse,
            DisputeStatus::Escalated,
        ]);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, [DisputeStatus::Resolved, DisputeStatus::Closed]);
    }

    public function canBeResolved(): bool
    {
        return $this->isOpen();
    }

    public function canBeEscalated(): bool
    {
        return in_array($this->status, [DisputeStatus::Open, DisputeStatus::UnderReview, DisputeStatus::AwaitingResponse]);
    }

    /** Is the given user a party to (or staff on) this dispute? */
    public function involves(User $user): bool
    {
        return $this->buyer_id === $user->id
            || $this->raised_by === $user->id
            || ($this->vendor && $this->vendor->user_id === $user->id);
    }

    // ─── SLA helpers ──────────────────────────────────────────────────────────

    /** An open dispute whose awaited-response deadline has passed. */
    public function isSlaOverdue(): bool
    {
        return $this->isOpen()
            && $this->response_due_at !== null
            && $this->response_due_at->isPast();
    }

    public function slaCountdownLabel(): ?string
    {
        if (! $this->response_due_at || ! $this->isOpen()) {
            return null;
        }

        return $this->response_due_at->isPast()
            ? 'overdue by '.$this->response_due_at->diffForHumans(null, true)
            : 'due '.$this->response_due_at->diffForHumans();
    }
}
