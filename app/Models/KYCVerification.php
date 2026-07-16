<?php

namespace App\Models;

use App\Casts\EncryptedDate;
use App\Enums\KYCDocumentType;
use App\Enums\KYCStatus;
use App\Enums\KYCType;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $reference
 * @property int $user_id
 * @property KYCType $type
 * @property KYCStatus $status
 * @property string $full_name
 * @property KYCDocumentType $id_type
 * @property string $id_number
 * @property Carbon|null $date_of_birth
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string $country
 * @property string|null $business_name
 * @property string|null $rc_number
 * @property string|null $document_front
 * @property string|null $document_back
 * @property string|null $selfie
 * @property string|null $proof_of_address
 * @property string|null $rejection_reason
 * @property int|null $reviewed_by
 * @property Carbon|null $submitted_at
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $reviewedBy
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereDocumentBack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereDocumentFront($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereIdNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereIdType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereProofOfAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereRcNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereSelfie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KYCVerification whereVerifiedAt($value)
 *
 * @mixin \Eloquent
 */
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
            'type' => KYCType::class,
            'status' => KYCStatus::class,
            'id_type' => KYCDocumentType::class,

            // PII encrypted at rest (see 2026_07_11 migration). Decryption is
            // transparent on read, so views/services are unaffected.
            'full_name' => 'encrypted',
            'id_number' => 'encrypted',
            'address' => 'encrypted',
            'rc_number' => 'encrypted',
            'date_of_birth' => EncryptedDate::class,

            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'verified_at' => 'datetime',
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
            'document_front' => $this->document_front,
            'document_back' => $this->document_back,
            'selfie' => $this->selfie,
            'proof_of_address' => $this->proof_of_address,
        ]);
    }
}
