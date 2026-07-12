<?php

namespace App\Services\Affiliate;

use App\Actions\Affiliate\ApproveCommissionAction;
use App\Actions\Affiliate\EnrollAffiliateAction;
use App\Actions\Affiliate\RecordClickAction;
use App\Actions\Affiliate\RecordConversionAction;
use App\Actions\Affiliate\RecordReferralSignupAction;
use App\Enums\AffiliateStatus;
use App\Models\AffiliateAccount;
use App\Models\AffiliateCommission;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\User;
use App\Repositories\Affiliate\AffiliateRepository;
use Illuminate\Http\Request;

class AffiliateService
{
    public function __construct(
        private EnrollAffiliateAction $enrollAction,
        private RecordClickAction $clickAction,
        private RecordReferralSignupAction $signupAction,
        private RecordConversionAction $conversionAction,
        private ApproveCommissionAction $approveAction,
        private AffiliateRepository $repo,
    ) {}

    public function enroll(User $user): AffiliateAccount
    {
        return $this->enrollAction->execute($user);
    }

    public function accountFor(User $user): ?AffiliateAccount
    {
        return $this->repo->accountForUser($user);
    }

    /** Track a click on a share link; returns false when the code is unknown/inactive. */
    public function trackClick(string $code, Request $request): bool
    {
        if (! settings('affiliate_enabled', true)) {
            return false;
        }

        $account = $this->repo->activeAccountByCode($code);

        if (! $account) {
            return false;
        }

        $this->clickAction->execute($account, $request);

        return true;
    }

    /** Hook: a new user registered (with users.referred_by already resolved). */
    public function recordSignup(User $newUser): ?Referral
    {
        return $this->signupAction->execute($newUser);
    }

    /**
     * Hook: a payment succeeded — reward the buyer's referrer AND the vendor's
     * referrer a share of the platform commission.
     *
     * @return AffiliateCommission[]
     */
    public function recordConversion(Payment $payment): array
    {
        return $this->conversionAction->execute($payment);
    }

    public function approveCommission(AffiliateCommission $commission): AffiliateCommission
    {
        return $this->approveAction->execute($commission);
    }

    public function cancelCommission(AffiliateCommission $commission, ?string $reason = null): AffiliateCommission
    {
        return $this->approveAction->cancel($commission, $reason);
    }

    public function setStatus(AffiliateAccount $account, AffiliateStatus $status): AffiliateAccount
    {
        $account->update(['status' => $status]);

        return $account;
    }

    // ─── Query passthroughs ─────────────────────────────────────────────────────

    public function commissionsFor(AffiliateAccount $account, int $perPage = 15)
    {
        return $this->repo->commissionsForAccount($account, $perPage);
    }

    public function referralsFor(AffiliateAccount $account, int $perPage = 15)
    {
        return $this->repo->referralsForAccount($account, $perPage);
    }

    public function accountsForAdmin(int $perPage = 20, array $filters = [])
    {
        return $this->repo->allAccountsForAdmin($perPage, $filters);
    }

    public function commissionsForAdmin(int $perPage = 20, array $filters = [])
    {
        return $this->repo->commissionsForAdmin($perPage, $filters);
    }

    public function topAffiliates(int $limit = 10)
    {
        return $this->repo->topAffiliates($limit);
    }

    public function pendingCommissionsCount(): int
    {
        return $this->repo->pendingCommissionsCount();
    }

    public function pendingPayoutTotal(): int
    {
        return $this->repo->pendingPayoutTotal();
    }
}
