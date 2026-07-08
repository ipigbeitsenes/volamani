<?php

namespace App\Actions\Vendors;

use App\Enums\Status;
use App\Enums\StrikeReason;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorStrike;
use App\Notifications\StrikeIssuedNotification;
use App\Notifications\VendorSuspendedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AddStrikeAction
{
    /**
     * Record a strike against a vendor. Recomputes the active-strike counter and
     * auto-suspends the store once it reaches the configured threshold. Safe to
     * call from dispute/chargeback resolution.
     */
    public function execute(
        Vendor $vendor,
        StrikeReason $reason,
        ?string $note = null,
        ?int $sourceId = null,
        ?User $issuedBy = null,
    ): VendorStrike {
        return DB::transaction(function () use ($vendor, $reason, $note, $sourceId, $issuedBy) {
            $strike = VendorStrike::create([
                'vendor_id' => $vendor->id,
                'reason'    => $reason,
                'source'    => $reason->source(),
                'source_id' => $sourceId,
                'note'      => $note,
                'issued_by' => $issuedBy?->id,
            ]);

            $active = $vendor->strikes()->active()->count();

            $vendor->update([
                'strikes'            => $active,
                'strikes_updated_at' => now(),
            ]);

            $suspended = false;

            if ($active >= $this->threshold() && $vendor->status === Status::Active) {
                $vendor->update([
                    'status'                => Status::Suspended,
                    'suspended_for_strikes' => true,
                ]);
                $suspended = true;
            }

            $this->notify($vendor, $strike, $suspended);

            return $strike->fresh();
        });
    }

    private function threshold(): int
    {
        $v = settings('strike_suspend_threshold');

        return (int) (($v === null || $v === '') ? config('protection.strike_suspend_threshold', 3) : $v);
    }

    private function notify(Vendor $vendor, VendorStrike $strike, bool $suspended): void
    {
        $admins = User::role('admin')->get();

        if ($vendor->user) {
            $vendor->user->notify(new StrikeIssuedNotification($strike));

            if ($suspended) {
                $vendor->user->notify(new VendorSuspendedNotification($vendor));
            }
        }

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new StrikeIssuedNotification($strike));
        }
    }
}
