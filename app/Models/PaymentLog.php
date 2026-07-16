<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $payment_id
 * @property string $event
 * @property string $gateway
 * @property string|null $gateway_reference
 * @property array<array-key, mixed>|null $payload
 * @property string|null $ip_address
 * @property bool $processed
 * @property Carbon $created_at
 * @property-read Payment|null $payment
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereGatewayReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereProcessed($value)
 *
 * @mixin \Eloquent
 */
class PaymentLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'payment_id', 'event', 'gateway', 'gateway_reference',
        'payload', 'ip_address', 'processed', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
