<?php

namespace App\Repositories\Reviews;

use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class ReviewRepository
{
    public function forReviewable(Model $reviewable, int $perPage = 10): LengthAwarePaginator
    {
        return Review::where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->getKey())
            ->where('is_approved', true)
            ->with('reviewer')
            ->latest()
            ->paginate($perPage);
    }

    public function forVendorUser(User $vendorUser, int $perPage = 15): LengthAwarePaginator
    {
        $vendor = $vendorUser->vendor;

        if (! $vendor) {
            return Review::whereRaw('1 = 0')->paginate($perPage);
        }

        return $vendor->reviews()
            ->with(['reviewer', 'reviewable'])
            ->latest()
            ->paginate($perPage);
    }

    public function allForAdmin(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Review::with(['reviewer', 'reviewable'])->latest();

        if (isset($filters['approved']) && $filters['approved'] !== '') {
            $query->where('is_approved', (bool) $filters['approved']);
        }

        if (! empty($filters['rating'])) {
            $query->where('rating', (int) $filters['rating']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('body', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->paginate($perPage);
    }
}
