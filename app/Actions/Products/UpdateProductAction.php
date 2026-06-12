<?php

namespace App\Actions\Products;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateProductAction
{
    public function execute(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $thumbnail = $product->thumbnail;
            if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
                if ($thumbnail) {
                    Storage::disk('public')->delete($thumbnail);
                }
                $thumbnail = $data['thumbnail']->store('products/thumbnails', 'public');
            }

            $needsReview = $product->status === ProductStatus::Active
                && (isset($data['price']) || isset($data['name']) || isset($data['files']));

            $product->update([
                'category_id'        => $data['category_id'] ?? $product->category_id,
                'name'               => $data['name'] ?? $product->name,
                'short_description'  => $data['short_description'] ?? $product->short_description,
                'description'        => $data['description'] ?? $product->description,
                'type'               => $data['type'] ?? $product->type,
                'price'              => isset($data['price']) ? to_kobo($data['price']) : $product->price,
                'compare_price'      => isset($data['compare_price']) ? to_kobo($data['compare_price']) : $product->compare_price,
                'thumbnail'          => $thumbnail,
                'preview_url'        => $data['preview_url'] ?? $product->preview_url,
                'download_limit'     => $data['download_limit'] ?? $product->download_limit,
                'download_expiry_hours' => $data['download_expiry_hours'] ?? $product->download_expiry_hours,
                'status'             => $needsReview ? ProductStatus::Pending : $product->status,
                'seo_title'          => $data['seo_title'] ?? $product->seo_title,
                'seo_description'    => $data['seo_description'] ?? $product->seo_description,
            ]);

            if (isset($data['tags'])) {
                $product->tags()->sync($data['tags']);
            }

            if (!empty($data['gallery'])) {
                foreach ($data['gallery'] as $index => $image) {
                    if ($image instanceof UploadedFile) {
                        $path = $image->store('products/gallery', 'public');
                        ProductGallery::create([
                            'product_id' => $product->id,
                            'path'       => $path,
                            'sort_order' => $product->gallery()->max('sort_order') + 1,
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
                            'sort_order'    => $product->files()->max('sort_order') + 1,
                        ]);
                    }
                }
            }

            return $product->fresh(['category', 'tags', 'gallery', 'files']);
        });
    }
}
