<?php

namespace App\Console\Commands;

use App\Services\Escrow\EscrowService;
use Illuminate\Console\Command;

class ReleaseExpiredEscrows extends Command
{
    protected $signature = 'escrow:auto-release';

    protected $description = 'Release escrows whose buyer-protection window has elapsed without a dispute';

    public function handle(EscrowService $escrowService): int
    {
        $released = $escrowService->processAutoReleases();

        $this->info("Auto-released {$released} escrow(s).");

        return self::SUCCESS;
    }
}
