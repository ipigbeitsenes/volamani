<?php

namespace App\Actions\Products;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductGallery;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateProductAction
{
    public function execute(Vendor $vendor, array $data): Product
    {
        return DB::transaction(function () use ($vendor, $data) {
            $thumbnail = null;
            if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
                $thumbnail = $data['thumbnail']->store('products/thumbnails', 'public');
            }

            $product = Product::create([
                'vendor_id'          => $vendor->id,
                'category_id'        => $data['category_id'],
                'name'               => $data['name'],
                'short_description'  => $data['short_description'] ?? null,
                'description'        => $data['description'],
                'type'               => $data['type'],
                'price'              => to_kobo($data['price']),
                'compare_price'      => isset($data['compare_price']) ? to_kobo($data['compare_price']) : null,
                'thumbnail'          => $thumbnail,
                'preview_url'        => $data['preview_url'] ?? null,
                'is_downloadable'    => $data['is_downloadable'] ?? true,
                'download_limit'     => $data['download_limit'] ?? null,
                'download_expiry_hours' => $data['download_expiry_hours'] ?? 48,
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
}
