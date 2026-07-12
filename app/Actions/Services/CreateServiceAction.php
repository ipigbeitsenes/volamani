<?php

namespace App\Actions\Services;

use App\Enums\ProductStatus;
use App\Models\FreelanceService;
use App\Models\ServiceFaq;
use App\Models\ServicePackage;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateServiceAction
{
    public function execute(Vendor $vendor, array $data): FreelanceService
    {
        $this->assertWithinListingLimit($vendor);

        return DB::transaction(function () use ($vendor, $data) {
            $thumbnail = null;
            if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
                $thumbnail = $data['thumbnail']->store('services/thumbnails', 'public');
            }

            $service = FreelanceService::create([
                'vendor_id' => $vendor->id,
                'category_id' => $data['category_id'] ?? null,
                'title' => $data['title'],
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'],
                'thumbnail' => $thumbnail,
                'status' => ProductStatus::Pending,
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
            ]);

            $this->syncPackages($service, $data['packages'] ?? []);
            $this->syncFaqs($service, $data['faqs'] ?? []);

            return $service->load(['packages', 'faqs']);
        });
    }

    /** Trust-tier cap on how many active listings a vendor may have. */
    private function assertWithinListingLimit(Vendor $vendor): void
    {
        $tier = $vendor->trustTier();
        $max = $tier->maxActiveListings();

        abort_if($max !== null && $vendor->activeListingCount() >= $max, 422,
            "You've reached the {$tier->label()} limit of {$max} active listings. This cap grows as your store earns trust."
        );
    }

    private function syncPackages(FreelanceService $service, array $packages): void
    {
        foreach ($packages as $pkgData) {
            if (empty($pkgData['name']) || empty($pkgData['price'])) {
                continue;
            }
            ServicePackage::create([
                'service_id' => $service->id,
                'tier' => $pkgData['tier'],
                'name' => $pkgData['name'],
                'description' => $pkgData['description'] ?? '',
                'price' => to_kobo($pkgData['price']),
                'delivery_days' => $pkgData['delivery_days'] ?? 3,
                'revisions' => $pkgData['revisions'] ?? 1,
                'features' => array_filter(explode("\n", $pkgData['features'] ?? '')),
            ]);
        }
    }

    private function syncFaqs(FreelanceService $service, array $faqs): void
    {
        foreach ($faqs as $index => $faqData) {
            if (empty($faqData['question'])) {
                continue;
            }
            ServiceFaq::create([
                'service_id' => $service->id,
                'question' => $faqData['question'],
                'answer' => $faqData['answer'] ?? '',
                'sort_order' => $index,
            ]);
        }
    }
}
