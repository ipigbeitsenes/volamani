<?php

namespace App\Repositories\Services;

use App\Enums\ProductStatus;
use App\Models\FreelanceService;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FreelanceServiceRepository extends BaseRepository
{
    public function __construct(FreelanceService $model)
    {
        parent::__construct($model);
    }

    public function searchServices(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['vendor', 'category', 'packages'])->active();

        if (! empty($filters['q'])) {
            $query->search($filters['q']);
        }

        if (! empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (! empty($filters['delivery'])) {
            $query->whereHas('packages', fn ($q) => $q->where('delivery_days', '<=', $filters['delivery']));
        }

        if (! empty($filters['budget'])) {
            $query->whereHas('packages', fn ($q) => $q->where('price', '<=', to_kobo($filters['budget'])));
        }

        if (! empty($filters['vendor'])) {
            $query->whereHas('vendor', fn ($q) => $q->where('slug', $filters['vendor']));
        }

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'popular' => $query->orderByDesc('orders_count'),
            'top_rated' => $query->orderByDesc('average_rating'),
            default => $query->latest(),
        };

        return $query->paginate($perPage)->appends($filters);
    }

    public function featuredServices(int $limit = 8): Collection
    {
        return $this->model->with(['vendor', 'packages'])
            ->active()
            ->featured()
            ->limit($limit)
            ->get();
    }

    public function vendorServices(int $vendorId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('vendor_id', $vendorId)
            ->with(['category', 'packages'])
            ->latest()
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): ?FreelanceService
    {
        return $this->model->with(['vendor', 'category', 'packages', 'faqs'])
            ->where('slug', $slug)
            ->first();
    }

    public function relatedServices(FreelanceService $service, int $limit = 4): Collection
    {
        return $this->model->with(['vendor', 'packages'])
            ->active()
            ->where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->limit($limit)
            ->get();
    }

    public function pendingApproval(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['vendor', 'category'])
            ->where('status', ProductStatus::Pending)
            ->latest()
            ->paginate($perPage);
    }
}
