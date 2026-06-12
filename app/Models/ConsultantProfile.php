<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
            'expertise'    => 'array',
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

    public function canBeReviewedBy(?\App\Models\User $user): bool
    {
        return $user && app(\App\Services\Reviews\ReviewEligibilityService::class)->canReview($user, $this);
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
            ->where('status', \App\Enums\ConsultationSessionStatus::Confirmed)
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
                'label'    => $day,
                'slot'     => $slots->get($i + 1),
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
