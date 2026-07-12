<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
