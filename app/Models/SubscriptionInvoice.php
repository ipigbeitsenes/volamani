<?php

namespace App\Models;

use App\Enums\SubscriptionInvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int $subscription_id
 * @property int $plan_id
 * @property int $amount
 * @property SubscriptionInvoiceStatus $status
 * @property string|null $method
 * @property int|null $payment_id
 * @property int|null $wallet_ledger_id
 * @property Carbon|null $period_start
 * @property Carbon|null $period_end
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment|null $payment
 * @property-read SubscriptionPlan|null $plan
 * @property-read Subscription|null $subscription
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionInvoice whereWalletLedgerId($value)
 *
 * @mixin \Eloquent
 */
class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'reference', 'subscription_id', 'plan_id', 'amount', 'status', 'method',
        'payment_id', 'wallet_ledger_id', 'period_start', 'period_end', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionInvoiceStatus::class,
            'amount' => 'integer',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'paid_at' => 'datetime',
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
