<?php

namespace App\Repositories\Vendors;

use App\Models\Vendor;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorRepository extends BaseRepository
{
    public function __construct(Vendor $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug): ?Vendor
    {
        return $this->model->where('slug', $slug)->with('user')->first();
    }

    public function findByUsername(string $username): ?Vendor
    {
        return $this->model
            ->whereHas('user', fn ($q) => $q->where('username', $username))
            ->with('user')
            ->first();
    }

    public function featuredVendors(int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->where('status', 'active')
            ->where('is_featured', true)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function activeVendors(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('status', 'active')
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Public, browseable directory of active vendors for buyers to discover and
     * follow. Supports keyword search, category filter and sorting. Each row
     * carries an active_products_count for the card. Avoids DB-specific SQL so
     * it runs identically on MySQL and SQLite (tests).
     *
     * @param array{q?:string|null, category?:string|null, sort?:string|null} $filters
     */
    public function directory(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->where('status', 'active')
            ->with('user')
            ->withCount(['products as active_products_count' => fn ($q) => $q->where('status', 'active')]);

        if ($q = trim((string) ($filters['q'] ?? ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('business_name', 'like', "%{$q}%")
                    ->orWhere('tagline', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            });
        }

        if ($category = ($filters['category'] ?? null)) {
            $query->where('category', $category);
        }

        match ($filters['sort'] ?? 'popular') {
            'newest' => $query->latest(),
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('followers_count'),
            default  => $query->orderByDesc('followers_count')->orderByDesc('average_rating'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function pendingVendors(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();
    }
}
