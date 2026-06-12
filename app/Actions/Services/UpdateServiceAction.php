<?php

namespace App\Actions\Services;

use App\Enums\ProductStatus;
use App\Models\FreelanceService;
use App\Models\ServiceFaq;
use App\Models\ServicePackage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateServiceAction
{
    public function execute(FreelanceService $service, array $data): FreelanceService
    {
        return DB::transaction(function () use ($service, $data) {
            $thumbnail = $service->thumbnail;
            if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
                if ($thumbnail) {
                    Storage::disk('public')->delete($thumbnail);
                }
                $thumbnail = $data['thumbnail']->store('services/thumbnails', 'public');
            }

            $needsReview = $service->status === ProductStatus::Active
                && isset($data['title']);

            $service->update([
                'category_id'       => $data['category_id'] ?? $service->category_id,
                'title'             => $data['title'] ?? $service->title,
                'short_description' => $data['short_description'] ?? $service->short_description,
                'description'       => $data['description'] ?? $service->description,
                'thumbnail'         => $thumbnail,
                'status'            => $needsReview ? ProductStatus::Pending : $service->status,
                'seo_title'         => $data['seo_title'] ?? $service->seo_title,
                'seo_description'   => $data['seo_description'] ?? $service->seo_description,
            ]);

            if (isset($data['packages'])) {
                $service->packages()->forceDelete();
                foreach ($data['packages'] as $pkgData) {
                    if (empty($pkgData['name']) || empty($pkgData['price'])) {
                        continue;
                    }
                    ServicePackage::create([
                        'service_id'    => $service->id,
                        'tier'          => $pkgData['tier'],
                        'name'          => $pkgData['name'],
                        'description'   => $pkgData['description'] ?? '',
                        'price'         => to_kobo($pkgData['price']),
                        'delivery_days' => $pkgData['delivery_days'] ?? 3,
                        'revisions'     => $pkgData['revisions'] ?? 1,
                        'features'      => array_filter(explode("\n", $pkgData['features'] ?? '')),
                    ]);
                }
            }

            if (isset($data['faqs'])) {
                $service->faqs()->delete();
                foreach ($data['faqs'] as $index => $faqData) {
                    if (empty($faqData['question'])) {
                        continue;
                    }
                    ServiceFaq::create([
                        'service_id' => $service->id,
                        'question'   => $faqData['question'],
                        'answer'     => $faqData['answer'] ?? '',
                        'sort_order' => $index,
                    ]);
                }
            }

            return $service->fresh(['packages', 'faqs']);
        });
    }
}
