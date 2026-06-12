<?php

namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'status'               => MatchStatus::class,
            'score'                => 'integer',
            'score_breakdown'      => 'array',
            'requester_interested' => 'boolean',
            'vendor_interested'    => 'boolean',
            'viewed_at'            => 'datetime',
            'connected_at'         => 'datetime',
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
            default            => 'secondary',
        };
    }
}
