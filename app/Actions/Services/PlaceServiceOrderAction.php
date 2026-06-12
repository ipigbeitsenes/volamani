<?php

namespace App\Actions\Services;

use App\Enums\PaymentStatus;
use App\Enums\ServiceOrderStatus;
use App\Models\FreelanceService;
use App\Models\ServiceOrder;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlaceServiceOrderAction
{
    public function execute(FreelanceService $service, ServicePackage $package, User $buyer): ServiceOrder
    {
        return DB::transaction(function () use ($service, $package, $buyer) {
            $commissionRate  = $service->vendor->getEffectiveCommissionRate();
            $platformFee     = (int) round($package->price * ($commissionRate / 100));
            $vendorEarnings  = $package->price - $platformFee;

            return ServiceOrder::create([
                'service_id'       => $service->id,
                'package_id'       => $package->id,
                'buyer_id'         => $buyer->id,
                'vendor_id'        => $service->vendor_id,
                'status'           => ServiceOrderStatus::Pending,
                'payment_status'   => PaymentStatus::Pending,
                'total_amount'     => $package->price,
                'platform_fee'     => $platformFee,
                'vendor_earnings'  => $vendorEarnings,
                'revisions_allowed' => $package->revisions,
            ]);
        });
    }
}
