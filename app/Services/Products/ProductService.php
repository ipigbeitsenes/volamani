<?php

namespace App\Services\Products;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\UpdateProductAction;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductGallery;
use App\Models\User;
use App\Models\Vendor;
use App\Services\BaseService;
use Illuminate\Support\Facades\Storage;

class ProductService extends BaseService
{
    public function __construct(
        private CreateProductAction $createAction,
        private UpdateProductAction $updateAction,
    ) {}

    public function createProduct(Vendor $vendor, array $data): Product
    {
        return $this->createAction->execute($vendor, $data);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        return $this->updateAction->execute($product, $data);
    }

    public function deleteGalleryImage(ProductGallery $image): void
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }

    public function deleteProductFile(ProductFile $file): void
    {
        Storage::disk('private')->delete($file->path);
        $file->delete();
    }

    public function approveProduct(Product $product, User $admin): Product
    {
        // Only announce to followers the FIRST time a product goes public,
        // so re-approvals (e.g. after an edit) don't re-notify everyone.
        $firstApproval = $product->approved_at === null;

        $product->update([
            'status'      => ProductStatus::Active,
            'approved_at' => now(),
            'approved_by' => $admin->id,
            'rejection_reason' => null,
        ]);

        if ($firstApproval) {
            // Resolved lazily to avoid a constructor dependency cycle.
            app(\App\Services\Social\FollowService::class)->announceNewProduct($product);
        }

        return $product;
    }

    public function rejectProduct(Product $product, User $admin, string $reason): Product
    {
        $product->update([
            'status'           => ProductStatus::Rejected,
            'rejection_reason' => $reason,
            'approved_by'      => $admin->id,
        ]);

        return $product;
    }

    public function archiveProduct(Product $product): void
    {
        $product->update(['status' => ProductStatus::Archived]);
    }
}
