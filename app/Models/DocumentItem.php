<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $document_id
 * @property string $description
 * @property numeric $quantity
 * @property int $unit_price
 * @property int $amount
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Document|null $document
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class DocumentItem extends Model
{
    protected $fillable = [
        'document_id', 'description', 'quantity', 'unit_price', 'amount', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'integer',
            'amount' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
