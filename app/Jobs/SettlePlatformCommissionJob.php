<?php

namespace App\Jobs;

use App\Actions\Commission\SettlePlatformCommissionAction;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

/**
 * Settles the platform commission for a delivered order off the request thread.
 * Idempotent via the platform_commissions ledger, so retries and duplicate
 * dispatches are safe; retries with back-off and dead-letters to failed_jobs.
 */
class SettlePlatformCommissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 120;

    public function __construct(public int $orderId) {}

    public function backoff(): array
    {
        return [10, 30, 60, 120];
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping("commission:order:{$this->orderId}"))->expireAfter(180)];
    }

    public function handle(SettlePlatformCommissionAction $settle): void
    {
        $order = Order::find($this->orderId);

        if ($order) {
            $settle->execute($order);
        }
    }
}
