<?php

namespace App\Console\Commands;

use App\Services\Subscription\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature = 'subscriptions:process';

    protected $description = 'Renew due subscriptions from the vendor wallet and expire lapsed ones';

    public function handle(SubscriptionService $service): int
    {
        $result = $service->processBillingCycle();

        $this->info("Subscriptions processed — renewed: {$result['renewed']}, expired: {$result['expired']}, failed: {$result['failed']}.");

        return self::SUCCESS;
    }
}
