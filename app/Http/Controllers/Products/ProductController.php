<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\PhysicalCategory;
use App\Repositories\Products\CategoryRepository;
use App\Repositories\Products\ProductRepository;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepository $productRepo,
        private CategoryRepository $categoryRepo,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'kind', 'category', 'physical_category', 'type', 'min_price', 'max_price', 'vendor', 'sort', 'in_stock']);
        $products = $this->productRepo->searchProducts($filters);
        $categories = $this->categoryRepo->rootCategories();
        $physicalCategories = PhysicalCategory::active()->roots()
            ->with(['children' => fn ($q) => $q->active()->orderBy('name')])
            ->orderBy('sort_order')->orderBy('name')->get();
        $featured = $this->productRepo->featuredProducts(4);

        return view('marketplace.products.index', compact('products', 'categories', 'physicalCategories', 'featured', 'filters'));
    }

    public function show(string $slug)
    {
        $product = $this->productRepo->findBySlug($slug);

        if (! $product || ! $product->isActive()) {
            abort(404);
        }

        $product->incrementViews();

        $related = $this->productRepo->relatedProducts($product);
        $hasPurchased = auth()->check() && $product->hasPurchased(auth()->user());

        return view('marketplace.products.show', compact('product', 'related', 'hasPurchased'));
    }
}
