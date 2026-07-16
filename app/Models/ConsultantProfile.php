<?php

namespace App\Models;

use App\Enums\ConsultationSessionStatus;
use App\Services\Reviews\ReviewEligibilityService;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_id
 * @property string $slug
 * @property string $display_name
 * @property string $bio
 * @property string|null $niche
 * @property array<array-key, mixed>|null $expertise
 * @property int $experience_years
 * @property string|null $linkedin
 * @property string|null $calendly_url
 * @property bool $is_available
 * @property numeric $average_rating
 * @property-read int|null $reviews_count
 * @property int $total_sessions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ConsultationPackage> $allPackages
 * @property-read int|null $all_packages_count
 * @property-read Collection<int, ConsultantAvailability> $availability
 * @property-read int|null $availability_count
 * @property-read string $avatar_url
 * @property-read Collection<int, ConsultationPackage> $packages
 * @property-read int|null $packages_count
 * @property-read Collection<int, Review> $reviews
 * @property-read Collection<int, ConsultationSession> $sessions
 * @property-read int|null $sessions_count
 * @property-read Collection<int, ConsultationSession> $upcomingSessions
 * @property-read int|null $upcoming_sessions_count
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereAverageRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereCalendlyUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereExperienceYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereExpertise($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereLinkedin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereNiche($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereReviewsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereTotalSessions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantProfile whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class ConsultantProfile extends Model
{
    use HasSlug;

    protected $table = 'consultant_profiles';

    protected $fillable = [
        'vendor_id', 'slug', 'display_name', 'bio', 'niche',
        'expertise', 'experience_years', 'linkedin', 'calendly_url',
        'is_available', 'average_rating', 'reviews_count', 'total_sessions',
    ];

    protected function casts(): array
    {
        return [
            'expertise' => 'array',
            'is_available' => 'boolean',
        ];
    }

    public function getSlugSource(): string
    {
        return $this->display_name;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ConsultationPackage::class, 'profile_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price');
    }

    public function allPackages(): HasMany
    {
        return $this->hasMany(ConsultationPackage::class, 'profile_id')
            ->orderBy('sort_order');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(ConsultantAvailability::class, 'profile_id')
            ->where('is_active', true)
            ->orderBy('day_of_week');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ConsultationSession::class, 'profile_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')
            ->where('is_approved', true);
    }

    public function canBeReviewedBy(?User $user): bool
    {
        return $user && app(ReviewEligibilityService::class)->canReview($user, $this);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('display_name', 'like', "%{$term}%")
                ->orWhere('bio', 'like', "%{$term}%")
                ->orWhere('niche', 'like', "%{$term}%");
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getAvatarUrlAttribute(): string
    {
        return $this->vendor->logo_url;
    }

    public function upcomingSessions(): HasMany
    {
        return $this->sessions()
            ->where('status', ConsultationSessionStatus::Confirmed)
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at');
    }

    public function availabilityByDay(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $slots = $this->availability->keyBy('day_of_week');
        $result = [];
        foreach ($days as $i => $day) {
            $result[$i + 1] = [
                'label' => $day,
                'slot' => $slots->get($i + 1),
            ];
        }

        return $result;
    }

    /** Lowest active package price (kobo), or 0 if none. */
    public function lowestPrice(): int
    {
        return (int) ($this->relationLoaded('packages')
            ? ($this->packages->min('price') ?? 0)
            : ($this->packages()->min('price') ?? 0));
    }
}
