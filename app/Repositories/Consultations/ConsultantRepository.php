<?php

namespace App\Repositories\Consultations;

use App\Models\ConsultantProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ConsultantRepository
{
    public function searchConsultants(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = ConsultantProfile::with(['vendor', 'packages'])
            ->whereHas('vendor', fn (Builder $q) => $q->where('status', 'active'))
            ->where('is_available', true);

        if (!empty($filters['q'])) {
            $term = '%' . $filters['q'] . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('display_name', 'like', $term)
                  ->orWhere('niche', 'like', $term)
                  ->orWhere('bio', 'like', $term);
            });
        }

        if (!empty($filters['niche'])) {
            $query->where('niche', 'like', '%' . $filters['niche'] . '%');
        }

        if (!empty($filters['min_experience'])) {
            $query->where('experience_years', '>=', (int) $filters['min_experience']);
        }

        if (!empty($filters['max_price'])) {
            $maxKobo = to_kobo((float) $filters['max_price']);
            $query->whereHas('packages', fn (Builder $q) => $q->where('is_active', true)->where('price', '<=', $maxKobo));
        }

        $sort = $filters['sort'] ?? 'rating';
        match ($sort) {
            'sessions' => $query->orderByDesc('total_sessions'),
            'newest'   => $query->latest(),
            default    => $query->orderByDesc('average_rating'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function featuredConsultants(int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        return ConsultantProfile::with(['vendor', 'packages'])
            ->whereHas('vendor', fn (Builder $q) => $q->where('status', 'active'))
            ->where('is_available', true)
            ->orderByDesc('average_rating')
            ->orderByDesc('total_sessions')
            ->limit($limit)
            ->get();
    }

    public function findBySlug(string $slug): ?ConsultantProfile
    {
        return ConsultantProfile::with(['vendor', 'packages', 'availability', 'reviews.reviewer'])
            ->where('slug', $slug)
            ->first();
    }

    public function findByVendor(int $vendorId): ?ConsultantProfile
    {
        return ConsultantProfile::with(['packages', 'availability'])
            ->where('vendor_id', $vendorId)
            ->first();
    }

    public function uniqueNiches(): array
    {
        return ConsultantProfile::whereNotNull('niche')
            ->distinct()
            ->pluck('niche')
            ->sort()
            ->values()
            ->toArray();
    }
}
