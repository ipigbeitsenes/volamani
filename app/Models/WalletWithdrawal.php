<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletWithdrawal extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'reference', 'wallet_id', 'user_id', 'amount', 'fee', 'net_amount',
        'bank_name', 'account_name', 'account_number', 'bank_code',
        'status', 'admin_notes', 'processed_by', 'processed_at', 'paystack_transfer_code',
    ];

    protected function casts(): array
    {
        return [
            'status'       => WithdrawalStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->reference)) {
                $model->reference = generate_reference('WDR');
            }
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === WithdrawalStatus::Pending;
    }

    public function canBeProcessed(): bool
    {
        return in_array($this->status, [WithdrawalStatus::Pending, WithdrawalStatus::Processing]);
    }
}
