<?php

namespace App\Models;

use App\Enums\KYCStatus;
use App\Enums\NotificationCategory;
use App\Enums\UserType;
use App\Traits\Auditable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, Auditable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'avatar',
        'bio',
        'location',
        'whatsapp',
        'user_type',
        'is_active',
        'kyc_status',
        'referral_code',
        'referred_by',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
        'buyer_strikes',
        'buyer_strikes_updated_at',
        'buyer_flagged',
        'buyer_flagged_at',
        'purchases_suspended',
        'purchases_suspended_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'locked_until'      => 'datetime',
            'is_active'         => 'boolean',
            'user_type'         => UserType::class,
            'kyc_status'        => KYCStatus::class,
            'password'          => 'hashed',
            'buyer_strikes'            => 'integer',
            'buyer_strikes_updated_at' => 'datetime',
            'buyer_flagged'            => 'boolean',
            'buyer_flagged_at'         => 'datetime',
            'purchases_suspended'      => 'boolean',
            'purchases_suspended_at'   => 'datetime',
        ];
    }

    // ─── Buyer abuse standing ───────────────────────────────────────────────

    public function buyerStrikes(): HasMany
    {
        return $this->hasMany(BuyerStrike::class);
    }

    /** Blocked from new purchases / disputes for repeated buyer-protection abuse. */
    public function purchasesSuspended(): bool
    {
        return (bool) $this->purchases_suspended;
    }

    /** Flagged for admin review (soft) but not yet blocked. */
    public function isFlaggedBuyer(): bool
    {
        return (bool) $this->buyer_flagged;
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(Str::random(8));
            }
            if (empty($user->username)) {
                $user->username = static::generateUsername($user->name ?: Str::before((string) $user->email, '@'));
            }
        });
    }

    /**
     * Build a unique, URL-safe username from a seed (name or email local part).
     * Guarantees every user has a username — it is the public storefront handle
     * (store/{username}) that several views route() on, so a null one 500s pages.
     */
    public static function generateUsername(string $seed): string
    {
        $base = Str::lower(preg_replace('/[^A-Za-z0-9]/', '', $seed));
        $base = $base !== '' ? substr($base, 0, 20) : 'user';

        $username = $base;
        $i = 1;
        while (static::withTrashed()->where('username', $username)->exists()) {
            $username = $base . $i;
            $i++;
        }

        return $username;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function kycVerification(): HasOne
    {
        return $this->hasOne(KYCVerification::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function affiliateAccount(): HasOne
    {
        return $this->hasOne(AffiliateAccount::class);
    }

    public function matchRequests(): HasMany
    {
        return $this->hasMany(MatchRequest::class);
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function securityLogs(): HasMany
    {
        return $this->hasMany(SecurityLog::class)->latest('created_at');
    }

    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /** Vendors this user follows (social commerce). */
    public function followedVendors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'follows', 'follower_id', 'vendor_id')->withTimestamps();
    }

    public function isFollowing(Vendor $vendor): bool
    {
        return $this->follows()->where('vendor_id', $vendor->id)->exists();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Should this user receive the given category on the given channel
     * ('email' or 'database')? Falls back to the category defaults when the
     * user has no explicit preference row. Essential categories are forced on.
     */
    public function wantsNotification(NotificationCategory $category, string $channel): bool
    {
        if ($category->isEssential()) {
            return true;
        }

        $pref = $this->notificationPreferences->firstWhere('category', $category);

        if (! $pref) {
            return $channel === 'email' ? $category->defaultEmail() : $category->defaultDatabase();
        }

        return $channel === 'email' ? (bool) $pref->email : (bool) $pref->database;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isVendor(): bool
    {
        return $this->hasRole('vendor');
    }

    public function isKYCVerified(): bool
    {
        return $this->kyc_status === KYCStatus::Verified;
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function getAvatarUrlAttribute(): string
    {
        return media_url($this->avatar)
            ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&size=80&background=1a56db&color=fff';
    }

    public function getStorefrontUrlAttribute(): string
    {
        return $this->username
            ? route('storefront.show', $this->username)
            : '#';
    }
}
