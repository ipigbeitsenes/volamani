<?php

namespace App\Actions\Commission;

use App\Enums\NotificationCategory;
use App\Enums\PlatformCommissionStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\PlatformCommission;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Notifications\NotificationService;
use App\Services\Wallet\WalletService;

/**
 * Records and settles the platform's commission on an order into the
 * platform_commissions ledger. This is the single home for the "does the
 * platform take a cut, and how" policy — keyed on the wallet/escrow toggles:
 *
 *   - wallet on + balance covers it  -> debit seller wallet   (Settled)
 *   - wallet off, escrow on          -> record as owed         (Owed)
 *   - wallet off AND escrow off      -> subscription-only mode (Waived)
 *   - zero fee / no seller account   -> Waived / Owed
 *
 * Idempotent: one row per order (unique order_id); a resolved (Settled/Waived)
 * entry is never re-processed, so repeated delivery/confirm calls are safe.
 */
class SettlePlatformCommissionAction
{
    public function __construct(
        private WalletService $wallet,
        private NotificationService $notifications,
    ) {}

    public function execute(Order $order): PlatformCommission
    {
        $entry = PlatformCommission::firstOrCreate(
            ['order_id' => $order->id],
            [
                'vendor_id' => $order->vendor_id,
                'amount' => (int) $order->platform_fee,
                'currency' => $order->currency ?: currency_code(),
                'status' => PlatformCommissionStatus::Pending,
            ],
        );

        // Idempotent: a resolved entry is never re-settled.
        if (in_array($entry->status, [PlatformCommissionStatus::Settled, PlatformCommissionStatus::Waived], true)) {
            return $entry;
        }

        $commission = (int) $entry->amount;

        if ($commission <= 0) {
            return $this->waive($entry, 'no_commission');
        }

        // Subscription-only mode: the platform runs on seller subscriptions and
        // takes no commission until wallet or escrow is turned back on.
        if (! feature('wallet') && ! feature('escrow')) {
            return $this->waive($entry, 'subscription_only');
        }

        $vendor = $order->vendor;
        $owner = $vendor instanceof Vendor ? $vendor->user : null;

        if (! $owner instanceof User) {
            $entry->update(['status' => PlatformCommissionStatus::Owed, 'reason' => 'no_seller_account']);

            return $entry;
        }

        // Collect from the seller wallet when that subsystem is on and can cover it.
        if (feature('wallet')) {
            $wallet = $this->wallet->getOrCreate($owner);

            if ($wallet->canWithdraw($commission)) {
                $this->wallet->debit(
                    $wallet,
                    $commission,
                    TransactionType::Commission,
                    "Platform commission — order {$order->reference}",
                    $entry,
                );

                $entry->update([
                    'status' => PlatformCommissionStatus::Settled,
                    'method' => 'wallet',
                    'settled_at' => now(),
                ]);

                return $entry;
            }
        }

        // Wallet off (escrow on) or insufficient balance → owed; nudge the seller.
        $entry->update([
            'status' => PlatformCommissionStatus::Owed,
            'method' => 'cash_pod',
            'reason' => feature('wallet') ? 'insufficient_wallet_balance' : 'wallet_disabled',
        ]);

        $this->notifyOwed($order, $owner, $commission);

        return $entry;
    }

    private function waive(PlatformCommission $entry, string $reason): PlatformCommission
    {
        $entry->update([
            'status' => PlatformCommissionStatus::Waived,
            'reason' => $reason,
            'settled_at' => now(),
        ]);

        return $entry;
    }

    private function notifyOwed(Order $order, User $owner, int $commission): void
    {
        [$closing, $url, $label] = feature('wallet')
            ? ['Please top up your wallet to clear it.', route('vendor.wallet.index'), 'Top up wallet']
            : ['Our team will reconcile it with you.', route('vendor.dashboard'), 'View dashboard'];

        $this->notifications->send(
            $owner,
            NotificationCategory::Payments,
            'Commission due',
            'A platform commission of '.money($commission).' is due on your delivered order '.$order->reference.'. '.$closing,
            $url,
            $label,
        );
    }
}
