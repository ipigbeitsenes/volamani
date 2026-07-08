<?php

namespace App\Actions\Products;

use App\Actions\Products\Concerns\SyncsPhysicalProduct;
use App\Enums\ProductKind;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductGallery;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    use SyncsPhysicalProduct;

    public function execute(Vendor $vendor, array $data): Product
    {
        $this->assertWithinListingLimit($vendor);

        return DB::transaction(function () use ($vendor, $data) {
            $isPhysical = ($data['kind'] ?? 'digital') === ProductKind::Physical->value;

            $thumbnail = null;
            if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
                $thumbnail = $data['thumbnail']->store('products/thumbnails', 'public');
            }

            $product = Product::create([
                'vendor_id'          => $vendor->id,
                'kind'               => $isPhysical ? ProductKind::Physical->value : ProductKind::Digital->value,
                'category_id'        => $isPhysical ? null : $data['category_id'],
                'physical_category_id' => $isPhysical ? $data['physical_category_id'] : null,
                'name'               => $data['name'],
                'short_description'  => $data['short_description'] ?? null,
                'description'        => $data['description'],
                // `type` is the digital sub-type; physical products park on 'other'.
                'type'               => $isPhysical ? 'other' : $data['type'],
                'price'              => to_kobo($data['price']),
                'compare_price'      => isset($data['compare_price']) ? to_kobo($data['compare_price']) : null,
                'thumbnail'          => $thumbnail,
                'preview_url'        => $data['preview_url'] ?? null,
                'is_downloadable'    => ! $isPhysical,
                'download_limit'     => $isPhysical ? null : ($data['download_limit'] ?? null),
                'download_expiry_hours' => $isPhysical ? 48 : ($data['download_expiry_hours'] ?? 48),
                'status'             => ProductStatus::Pending,
                'seo_title'          => $data['seo_title'] ?? null,
                'seo_description'    => $data['seo_description'] ?? null,
            ]);

            if (!empty($data['tags'])) {
                $product->tags()->sync($data['tags']);
            }

            if (!empty($data['gallery'])) {
                foreach ($data['gallery'] as $index => $image) {
                    if ($image instanceof UploadedFile) {
                        $path = $image->store('products/gallery', 'public');
                        ProductGallery::create([
                            'product_id' => $product->id,
                            'path'       => $path,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            if ($isPhysical) {
                $this->syncPhysical($product, $data);

                return $product->load(['physicalCategory', 'secondaryPhysicalCategories', 'tags', 'gallery', 'physicalDetail', 'variants']);
            }

            if (!empty($data['files'])) {
                foreach ($data['files'] as $index => $file) {
                    if ($file instanceof UploadedFile) {
                        $path = $file->store('products/files', 'private');
                        ProductFile::create([
                            'product_id'    => $product->id,
                            'label'         => $data['file_labels'][$index] ?? $file->getClientOriginalName(),
                            'path'          => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type'     => $file->getMimeType(),
                            'file_size'     => $file->getSize(),
                            'sort_order'    => $index,
                        ]);
                    }
                }
            }

            return $product->load(['category', 'tags', 'gallery', 'files']);
        });
    }

    /** Trust-tier cap on how many active listings a vendor may have. */
    private function assertWithinListingLimit(Vendor $vendor): void
    {
        $tier = $vendor->trustTier();
        $max  = $tier->maxActiveListings();

        abort_if($max !== null && $vendor->activeListingCount() >= $max, 422,
            "You've reached the {$tier->label()} limit of {$max} active listings. This cap grows as your store earns trust."
        );
    }
}
