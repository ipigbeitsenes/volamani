<?php

namespace App\Models;

use App\Enums\KYCDocumentType;
use App\Enums\KYCStatus;
use App\Enums\KYCType;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KYCVerification extends Model
{
    use Auditable;

    protected $table = 'kyc_verifications';

    protected $fillable = [
        'reference', 'user_id', 'type', 'status',
        'full_name', 'id_type', 'id_number', 'date_of_birth',
        'address', 'city', 'state', 'country',
        'business_name', 'rc_number',
        'document_front', 'document_back', 'selfie', 'proof_of_address',
        'rejection_reason', 'reviewed_by',
        'submitted_at', 'reviewed_at', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'type'          => KYCType::class,
            'status'        => KYCStatus::class,
            'id_type'       => KYCDocumentType::class,
            'date_of_birth' => 'date',
            'submitted_at'  => 'datetime',
            'reviewed_at'   => 'datetime',
            'verified_at'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (KYCVerification $kyc) {
            if (empty($kyc->reference)) {
                $kyc->reference = generate_reference('KYC');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === KYCStatus::Pending;
    }

    public function isVerified(): bool
    {
        return $this->status === KYCStatus::Verified;
    }

    public function canReview(): bool
    {
        return $this->status === KYCStatus::Pending;
    }

    /** The document fields that actually have a stored file (for admin review). */
    public function documents(): array
    {
        return array_filter([
            'document_front'   => $this->document_front,
            'document_back'    => $this->document_back,
            'selfie'           => $this->selfie,
            'proof_of_address' => $this->proof_of_address,
        ]);
    }
}
