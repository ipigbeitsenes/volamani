<?php

namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $match_request_id
 * @property int $vendor_id
 * @property int $score
 * @property array<array-key, mixed>|null $score_breakdown
 * @property MatchStatus $status
 * @property bool $requester_interested
 * @property bool $vendor_interested
 * @property Carbon|null $viewed_at
 * @property Carbon|null $connected_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read MatchRequest|null $matchRequest
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereConnectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereMatchRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereRequesterInterested($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereScoreBreakdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereVendorInterested($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessMatch whereViewedAt($value)
 *
 * @mixin \Eloquent
 */
class BusinessMatch extends Model
{
    protected $table = 'business_matches';

    protected $fillable = [
        'match_request_id', 'vendor_id', 'score', 'score_breakdown', 'status',
        'requester_interested', 'vendor_interested', 'viewed_at', 'connected_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => MatchStatus::class,
            'score' => 'integer',
            'score_breakdown' => 'array',
            'requester_interested' => 'boolean',
            'vendor_interested' => 'boolean',
            'viewed_at' => 'datetime',
            'connected_at' => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function matchRequest(): BelongsTo
    {
        return $this->belongsTo(MatchRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isConnected(): bool
    {
        return $this->status === MatchStatus::Connected;
    }

    public function isDeclined(): bool
    {
        return $this->status === MatchStatus::Declined;
    }

    public function scoreColor(): string
    {
        return match (true) {
            $this->score >= 75 => 'success',
            $this->score >= 50 => 'primary',
            $this->score >= 30 => 'warning',
            default => 'secondary',
        };
    }
}
