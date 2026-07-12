<?php

namespace App\Services\Pricing;

use App\Models\PricingAddOn;
use App\Models\PricingEstimate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PricingCalculatorService
{
    const URGENCY_MULTIPLIERS = [
        'normal' => 1.00,
        'soon' => 1.25,
        'urgent' => 1.50,
        'rush' => 2.00,
    ];

    public function calculate(array $data): array
    {
        $pricingType = $data['pricing_type'];
        $urgency = $data['urgency'] ?? 'normal';
        $multiplier = self::URGENCY_MULTIPLIERS[$urgency] ?? 1.00;

        // Base price computation
        $baseKobo = match ($pricingType) {
            'hourly' => (int) round(
                to_kobo((float) ($data['hourly_rate'] ?? 0)) * (float) ($data['estimated_hours'] ?? 0)
            ),
            'milestone' => $this->sumMilestones($data['milestones'] ?? []),
            default => to_kobo((float) ($data['base_price'] ?? 0)),
        };

        // Add-ons
        $addOnsTotal = 0;
        $resolvedAddOns = [];
        if (! empty($data['add_on_ids'])) {
            $addOns = PricingAddOn::whereIn('id', $data['add_on_ids'])->where('is_active', true)->get();
            foreach ($addOns as $addOn) {
                $addOnPrice = $addOn->calculateFor($baseKobo);
                $addOnsTotal += $addOnPrice;
                $resolvedAddOns[] = [
                    'id' => $addOn->id,
                    'name' => $addOn->name,
                    'price' => $addOnPrice,
                    'is_percentage' => $addOn->is_percentage,
                    'display_price' => $addOn->displayPrice(),
                ];
            }
        }

        $subtotal = $baseKobo + $addOnsTotal;
        $total = (int) round($subtotal * $multiplier);

        return [
            'pricing_type' => $pricingType,
            'urgency' => $urgency,
            'urgency_multiplier' => $multiplier,
            'base_price' => $baseKobo,
            'hourly_rate' => to_kobo((float) ($data['hourly_rate'] ?? 0)),
            'estimated_hours' => (float) ($data['estimated_hours'] ?? 0),
            'add_ons' => $resolvedAddOns,
            'add_ons_total' => $addOnsTotal,
            'milestones' => $data['milestones'] ?? [],
            'subtotal' => $subtotal,
            'total' => $total,
            'urgency_surcharge' => $total - $subtotal,
        ];
    }

    public function saveEstimate(array $data, array $breakdown, ?User $user = null, ?string $sessionToken = null): PricingEstimate
    {
        return DB::transaction(function () use ($data, $breakdown, $user, $sessionToken) {
            return PricingEstimate::create([
                'user_id' => $user?->id,
                'session_token' => $sessionToken,
                'template_id' => $data['template_id'] ?? null,
                'category' => $data['category'],
                'service_name' => $data['service_name'],
                'pricing_type' => $breakdown['pricing_type'],
                'urgency' => $breakdown['urgency'],
                'urgency_multiplier' => $breakdown['urgency_multiplier'],
                'base_price' => $breakdown['base_price'],
                'hourly_rate' => $breakdown['hourly_rate'],
                'estimated_hours' => $breakdown['estimated_hours'],
                'add_ons' => $breakdown['add_ons'],
                'add_ons_total' => $breakdown['add_ons_total'],
                'milestones' => $breakdown['milestones'],
                'subtotal' => $breakdown['subtotal'],
                'total' => $breakdown['total'],
                'notes' => $data['notes'] ?? null,
                'client_name' => $data['client_name'] ?? null,
                'client_email' => $data['client_email'] ?? null,
            ]);
        });
    }

    private function sumMilestones(array $milestones): int
    {
        return (int) array_sum(array_map(
            fn ($m) => to_kobo((float) ($m['amount'] ?? 0)),
            array_filter($milestones, fn ($m) => ! empty($m['amount']))
        ));
    }
}
