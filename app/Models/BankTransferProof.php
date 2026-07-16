<?php

namespace App\Models;

use App\Enums\BankTransferStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $payment_id
 * @property int $user_id
 * @property string $bank_name
 * @property string $account_name
 * @property int $amount
 * @property Carbon $transfer_date
 * @property string|null $proof_file
 * @property string|null $notes
 * @property BankTransferStatus $status
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property string|null $rejection_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment|null $payment
 * @property-read User|null $reviewer
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereProofFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereTransferDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankTransferProof whereUserId($value)
 *
 * @mixin \Eloquent
 */
class BankTransferProof extends Model
{
    protected $fillable = [
        'payment_id', 'user_id', 'bank_name', 'account_name',
        'amount', 'transfer_date', 'proof_file', 'notes',
        'status', 'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => BankTransferStatus::class,
            'transfer_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function proofUrl(): ?string
    {
        return media_url($this->proof_file);
    }
}
