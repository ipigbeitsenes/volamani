<?php

namespace App\Models;

use App\Enums\BankTransferStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
