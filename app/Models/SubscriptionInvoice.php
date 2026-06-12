<?php

namespace App\Models;

use App\Enums\SubscriptionInvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'reference', 'subscription_id', 'plan_id', 'amount', 'status', 'method',
        'payment_id', 'wallet_ledger_id', 'period_start', 'period_end', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => SubscriptionInvoiceStatus::class,
            'amount'       => 'integer',
            'period_start' => 'datetime',
            'period_end'   => 'datetime',
            'paid_at'      => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SubscriptionInvoice $invoice) {
            if (empty($invoice->reference)) {
                $invoice->reference = generate_reference('SBI');
            }
        });
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === SubscriptionInvoiceStatus::Paid;
    }
}
