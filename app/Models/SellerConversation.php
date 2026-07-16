<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $buyer_id
 * @property int $vendor_id
 * @property int|null $product_id
 * @property Carbon|null $last_message_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $buyer
 * @property-read SellerMessage|null $latestMessage
 * @property-read Collection<int, SellerMessage> $messages
 * @property-read int|null $messages_count
 * @property-read Product|null $product
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation forUser(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation whereLastMessageAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerConversation whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class SellerConversation extends Model
{
    protected $fillable = ['buyer_id', 'vendor_id', 'product_id', 'last_message_at'];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SellerMessage::class)->orderBy('id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(SellerMessage::class)->latestOfMany();
    }

    /** Whether the given user is a participant (the buyer or the vendor's owner). */
    public function includes(User $user): bool
    {
        return $this->buyer_id === $user->id || $this->isVendorSide($user);
    }

    /** True when $user is the vendor side of this thread. */
    public function isVendorSide(User $user): bool
    {
        $vendor = $this->vendor;

        return $vendor instanceof Vendor && $vendor->user_id === $user->id;
    }

    /** Display name of the other party, relative to $user. */
    public function counterpartName(User $user): string
    {
        if ($this->isVendorSide($user)) {
            $buyer = $this->buyer;

            return $buyer instanceof User ? $buyer->name : 'Buyer';
        }

        $vendor = $this->vendor;

        return $vendor instanceof Vendor ? $vendor->business_name : 'Seller';
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->count();
    }

    /** Threads the given user participates in (as buyer or as the vendor owner). */
    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('buyer_id', $user->id)
                ->orWhereHas('vendor', fn ($v) => $v->where('user_id', $user->id));
        });
    }
}
