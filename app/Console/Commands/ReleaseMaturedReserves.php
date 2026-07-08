<?php

namespace App\Console\Commands;

use App\Actions\Wallet\ReleaseReserveAction;
use App\Models\WalletReserve;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseMaturedReserves extends Command
{
    protected $signature = 'reserve:release';

    protected $description = 'Pay out rolling chargeback reserves whose holding window has elapsed';

    public function handle(ReleaseReserveAction $action): int
    {
        $released = 0;

        foreach (WalletReserve::dueForRelease()->get() as $reserve) {
            try {
                $action->execute($reserve);
                $released++;
            } catch (\Throwable $e) {
                Log::error("Reserve release failed for {$reserve->reference}: {$e->getMessage()}");
            }
        }

        $this->info("Released {$released} matured reserve(s).");

        return self::SUCCESS;
    }
}
