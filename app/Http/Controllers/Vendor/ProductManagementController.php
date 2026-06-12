<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\CreateProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\ProductFile;
use App\Models\ProductGallery;
use App\Repositories\Products\CategoryRepository;
use App\Repositories\Products\ProductRepository;
use App\Services\Products\ProductService;
use Illuminate\Http\Request;

class ProductManagementController extends Controller
{
    public function __construct(
        private ProductRepository $productRepo,
        private CategoryRepository $categoryRepo,
        private ProductService $productService,
    ) {}

    public function index(Request $request)
    {
        $vendor   = $request->user()->vendor;
        $products = $this->productRepo->vendorProducts($vendor->id);

        return view('vendor.products.index', compact('products'));
    }

    public function create()
    {
        $categories = $this->categoryRepo->allForSelect();
        return view('vendor.products.create', compact('categories'));
    }

    public function store(CreateProductRequest $request)
    {
        $vendor  = $request->user()->vendor;
        $product = $this->productService->createProduct($vendor, $request->validated());

        $this->flashSuccess('Product submitted for review.');
        return redirect()->route('vendor.products.index');
    }

    public function edit(Request $request, int $id)
    {
        $vendor  = $request->user()->vendor;
        $product = $this->productRepo->findOrFail($id);

        abort_unless($product->vendor_id === $vendor->id, 403);

        $categories = $this->categoryRepo->allForSelect();
        return view('vendor.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $product = $this->productRepo->findOrFail($id);
        $this->productService->updateProduct($product, $request->validated());

        $this->flashSuccess('Product updated successfully.');
        return redirect()->route('vendor.products.index');
    }

    public function destroy(Request $request, int $id)
    {
        $vendor  = $request->user()->vendor;
        $product = $this->productRepo->findOrFail($id);

        abort_unless($product->vendor_id === $vendor->id, 403);

        $this->productService->archiveProduct($product);
        $this->flashSuccess('Product archived.');
        return redirect()->route('vendor.products.index');
    }

    public function deleteGalleryImage(Request $request, int $imageId)
    {
        $image  = ProductGallery::findOrFail($imageId);
        $vendor = $request->user()->vendor;

        abort_unless($image->product->vendor_id === $vendor->id, 403);

        $this->productService->deleteGalleryImage($image);

        return response()->json(['success' => true]);
    }

    public function deleteFile(Request $request, int $fileId)
    {
        $file   = ProductFile::findOrFail($fileId);
        $vendor = $request->user()->vendor;

        abort_unless($file->product->vendor_id === $vendor->id, 403);

        $this->productService->deleteProductFile($file);

        return response()->json(['success' => true]);
    }
}
