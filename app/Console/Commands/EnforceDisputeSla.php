<?php

namespace App\Console\Commands;

use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Models\User;
use App\Repositories\Disputes\DisputeRepository;
use App\Services\Disputes\DisputeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnforceDisputeSla extends Command
{
    protected $signature = 'disputes:enforce-sla';

    protected $description = 'Auto-escalate (or auto-refund) disputes that have breached their response SLA';

    public function handle(DisputeRepository $repo, DisputeService $disputes): int
    {
        // The "system" acts as the first admin so escalation messages have an author.
        $systemAdmin = User::role('admin')->first();

        if (! $systemAdmin) {
            $this->warn('No admin user available to action SLA breaches.');
            return self::SUCCESS;
        }

        $autoRefund = $this->autoRefund();
        $actioned   = 0;

        foreach ($repo->dueForSla() as $dispute) {
            // Flag first so a failure below never re-loops the same breach endlessly.
            $dispute->update(['sla_breached' => true]);

            try {
                $vendorMissed = in_array($dispute->status, [DisputeStatus::Open, DisputeStatus::AwaitingResponse], true);

                if ($autoRefund && $vendorMissed && $dispute->escrow && $dispute->escrow->canRefund()) {
                    $disputes->resolve(
                        $dispute,
                        $systemAdmin,
                        DisputeResolution::RefundToBuyer,
                        null,
                        'Auto-resolved: the seller did not respond within the SLA window.',
                    );
                } elseif ($dispute->canBeEscalated()) {
                    $disputes->escalate(
                        $dispute,
                        $systemAdmin,
                        'Auto-escalated: response SLA breached.',
                    );
                }

                $actioned++;
            } catch (\Throwable $e) {
                Log::error("Dispute SLA enforcement failed for {$dispute->reference}: {$e->getMessage()}");
            }
        }

        $this->info("Actioned {$actioned} SLA-breached dispute(s).");

        return self::SUCCESS;
    }

    private function autoRefund(): bool
    {
        $v = settings('dispute_auto_refund_on_breach');

        if ($v === null || $v === '') {
            return (bool) config('protection.dispute_auto_refund_on_breach', false);
        }

        return filter_var($v, FILTER_VALIDATE_BOOLEAN);
    }
}
