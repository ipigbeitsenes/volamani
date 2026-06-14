<?php

namespace App\Repositories\Products;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function activeProducts(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['vendor', 'category'])
            ->active()
            ->latest()
            ->paginate($perPage);
    }

    public function searchProducts(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['vendor', 'category', 'physicalCategory', 'physicalDetail'])->active();

        if (!empty($filters['q'])) {
            $query->search($filters['q']);
        }

        // Fulfillment kind: digital vs physical.
        $kind = $filters['kind'] ?? null;
        if ($kind) {
            $query->where('kind', $kind);
        }

        // Digital-only filters (ignored when the user has narrowed to physical,
        // so a stale cross-kind param from switching tabs can't zero out results).
        if (!empty($filters['category']) && $kind !== 'physical') {
            $query->where('category_id', $filters['category']);
        }
        if (!empty($filters['type']) && $kind !== 'physical') {
            $query->ofType($filters['type']);
        }

        // Physical-only filters (ignored when narrowed to digital).
        if (!empty($filters['physical_category']) && $kind !== 'digital') {
            $query->where('physical_category_id', $filters['physical_category']);
        }
        if (!empty($filters['in_stock']) && $kind !== 'digital') {
            $query->whereHas('physicalDetail', fn ($q) => $q->where('stock_quantity', '>', 0));
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', to_kobo($filters['min_price']));
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', to_kobo($filters['max_price']));
        }

        if (!empty($filters['vendor'])) {
            $query->whereHas('vendor', fn($q) => $q->where('slug', $filters['vendor']));
        }

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'price_asc'    => $query->orderBy('price'),
            'price_desc'   => $query->orderByDesc('price'),
            'popular'      => $query->orderByDesc('sales_count'),
            'top_rated'    => $query->orderByDesc('average_rating'),
            default        => $query->latest(),
        };

        return $query->paginate($perPage)->appends($filters);
    }

    public function featuredProducts(int $limit = 8): Collection
    {
        return $this->model->with(['vendor', 'category'])
            ->active()
            ->featured()
            ->limit($limit)
            ->get();
    }

    public function vendorProducts(int $vendorId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('vendor_id', $vendorId)
            ->with(['category'])
            ->latest()
            ->paginate($perPage);
    }

    public function pendingApproval(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['vendor', 'category'])
            ->where('status', ProductStatus::Pending)
            ->latest()
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->model->with(['vendor', 'category', 'tags', 'gallery', 'files'])
            ->where('slug', $slug)
            ->first();
    }

    public function relatedProducts(Product $product, int $limit = 4): Collection
    {
        return $this->model->with(['vendor'])
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit($limit)
            ->get();
    }
}
