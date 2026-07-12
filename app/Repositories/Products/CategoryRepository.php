<?php

namespace App\Repositories\Products;

use App\Models\ProductCategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository
{
    public function __construct(ProductCategory $model)
    {
        parent::__construct($model);
    }

    public function rootCategories(): Collection
    {
        return $this->model->active()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    public function findBySlug(string $slug): ?ProductCategory
    {
        return $this->model->where('slug', $slug)->active()->first();
    }

    public function allForSelect(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }
}
