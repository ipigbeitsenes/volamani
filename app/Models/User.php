<?php

namespace App\Models;

use App\Enums\KYCStatus;
use App\Enums\NotificationCategory;
use App\Enums\UserType;
use App\Traits\Auditable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $username
 * @property string|null $phone
 * @property Carbon|null $phone_verified_at
 * @property string|null $avatar
 * @property string|null $bio
 * @property string|null $location
 * @property string|null $whatsapp
 * @property UserType $user_type
 * @property bool $is_active
 * @property KYCStatus $kyc_status
 * @property Carbon|null $terms_accepted_at
 * @property string|null $terms_version
 * @property int $buyer_strikes
 * @property Carbon|null $buyer_strikes_updated_at
 * @property bool $buyer_flagged
 * @property Carbon|null $buyer_flagged_at
 * @property bool $purchases_suspended
 * @property Carbon|null $purchases_suspended_at
 * @property string|null $referral_code
 * @property int|null $referred_by
 * @property Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property int $failed_login_attempts
 * @property Carbon|null $locked_until
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read AffiliateAccount|null $affiliateAccount
 * @property-read Collection<int, BuyerStrike> $buyerStrikes
 * @property-read int|null $buyer_strikes_count
 * @property-read Collection<int, Vendor> $followedVendors
 * @property-read int|null $followed_vendors_count
 * @property-read Collection<int, Follow> $follows
 * @property-read int|null $follows_count
 * @property-read string $avatar_url
 * @property-read string $storefront_url
 * @property-read KYCVerification|null $kycVerification
 * @property-read Collection<int, MatchRequest> $matchRequests
 * @property-read int|null $match_requests_count
 * @property-read Collection<int, NotificationPreference> $notificationPreferences
 * @property-read int|null $notification_preferences_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, User> $referrals
 * @property-read int|null $referrals_count
 * @property-read User|null $referrer
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, SecurityLog> $securityLogs
 * @property-read int|null $security_logs_count
 * @property-read Vendor|null $vendor
 * @property-read Wallet|null $wallet
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBuyerFlagged($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBuyerFlaggedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBuyerStrikes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBuyerStrikesUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFailedLoginAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKycStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLockedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePurchasesSuspended($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePurchasesSuspendedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereReferralCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereReferredBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTermsAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTermsVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Auditable, HasFactory, HasRoles, Notifiable, SoftDeletes;

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
        'terms_accepted_at',
        'terms_version',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'is_active' => 'boolean',
            'user_type' => UserType::class,
            'kyc_status' => KYCStatus::class,
            'password' => 'hashed',
            'buyer_strikes' => 'integer',
            'buyer_strikes_updated_at' => 'datetime',
            'buyer_flagged' => 'boolean',
            'buyer_flagged_at' => 'datetime',
            'purchases_suspended' => 'boolean',
            'purchases_suspended_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    // ─── Terms & Conditions acceptance ──────────────────────────────────────

    /** The Terms version currently in force (admin-overridable via settings). */
    public function currentTermsVersion(): string
    {
        $v = settings('terms_version');

        return (string) (($v === null || $v === '') ? config('legal.terms_version', '1') : $v);
    }

    /** Whether this user has accepted the Terms version currently in force. */
    public function hasAcceptedCurrentTerms(): bool
    {
        return $this->terms_accepted_at !== null
            && $this->terms_version === $this->currentTermsVersion();
    }

    /** Record acceptance of the current Terms version. */
    public function acceptTerms(): void
    {
        $this->forceFill([
            'terms_accepted_at' => now(),
            'terms_version' => $this->currentTermsVersion(),
        ])->save();
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
            $username = $base.$i;
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
    public function followedVendors(): BelongsToMany
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
            ?? 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&size=80&background=1a56db&color=fff';
    }

    public function getStorefrontUrlAttribute(): string
    {
        return $this->username
            ? route('storefront.show', $this->username)
            : '#';
    }
}
