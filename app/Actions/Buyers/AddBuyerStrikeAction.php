<?php

namespace App\Actions\Buyers;

use App\Enums\BuyerStrikeReason;
use App\Models\BuyerStrike;
use App\Models\User;
use App\Notifications\BuyerStrikeIssuedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Record an abuse strike against a buyer and recompute their standing. Reaching
 * the flag threshold marks the account for admin review; the suspend threshold
 * blocks new purchases and disputes. Safe to call from dispute / chargeback
 * resolution (buyer-side mirror of AddStrikeAction).
 */
class AddBuyerStrikeAction
{
    public function execute(
        User $buyer,
        BuyerStrikeReason $reason,
        ?string $note = null,
        ?int $sourceId = null,
        ?User $issuedBy = null,
    ): BuyerStrike {
        return DB::transaction(function () use ($buyer, $reason, $note, $sourceId, $issuedBy) {
            $strike = BuyerStrike::create([
                'user_id'   => $buyer->id,
                'reason'    => $reason,
                'source'    => $reason->source(),
                'source_id' => $sourceId,
                'note'      => $note,
                'issued_by' => $issuedBy?->id,
            ]);

            $active = $buyer->buyerStrikes()->active()->count();

            $flagged   = $active >= $this->flagThreshold();
            $suspended = $active >= $this->suspendThreshold();

            $buyer->update([
                'buyer_strikes'            => $active,
                'buyer_strikes_updated_at' => now(),
                'buyer_flagged'            => $flagged,
                'buyer_flagged_at'         => $flagged ? ($buyer->buyer_flagged_at ?? now()) : null,
                'purchases_suspended'      => $suspended,
                'purchases_suspended_at'   => $suspended ? ($buyer->purchases_suspended_at ?? now()) : null,
            ]);

            $this->notify($buyer, $strike, $suspended);

            return $strike->fresh();
        });
    }

    private function flagThreshold(): int
    {
        $v = settings('buyer_flag_threshold');

        return (int) (($v === null || $v === '') ? config('protection.buyer_flag_threshold', 2) : $v);
    }

    private function suspendThreshold(): int
    {
        $v = settings('buyer_suspend_threshold');

        return (int) (($v === null || $v === '') ? config('protection.buyer_suspend_threshold', 4) : $v);
    }

    private function notify(User $buyer, BuyerStrike $strike, bool $suspended): void
    {
        $buyer->notify(new BuyerStrikeIssuedNotification($strike, $suspended));

        $admins = User::role('admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new BuyerStrikeIssuedNotification($strike, $suspended));
        }
    }
}
