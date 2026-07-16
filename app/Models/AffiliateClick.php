<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $affiliate_account_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $landing_page
 * @property string|null $referrer_url
 * @property bool $converted
 * @property Carbon|null $converted_at
 * @property Carbon|null $created_at
 * @property-read AffiliateAccount|null $account
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick whereAffiliateAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick whereConverted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick whereConvertedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick whereLandingPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick whereReferrerUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateClick whereUserAgent($value)
 *
 * @mixin \Eloquent
 */
class AffiliateClick extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'affiliate_account_id', 'ip_address', 'user_agent',
        'landing_page', 'referrer_url', 'converted', 'converted_at', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'converted' => 'boolean',
            'converted_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AffiliateAccount::class, 'affiliate_account_id');
    }
}
