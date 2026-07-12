<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'reference', 'user_id', 'payable_type', 'payable_id',
        'gateway', 'gateway_reference', 'status', 'currency', 'amount',
        'metadata', 'ip_address',
        'paid_at', 'failed_at', 'refunded_at', 'refund_amount', 'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'gateway' => PaymentGateway::class,
            'status' => PaymentStatus::class,
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->reference)) {
                $model->reference = generate_reference('PAY');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    public function bankTransferProof(): HasMany
    {
        return $this->hasMany(BankTransferProof::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::Success;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    public function canBeRefunded(): bool
    {
        return $this->isSuccessful() && $this->gateway === PaymentGateway::Paystack;
    }
}
