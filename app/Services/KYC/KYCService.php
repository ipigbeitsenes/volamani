<?php

namespace App\Services\KYC;

use App\Actions\KYC\ApproveKYCAction;
use App\Actions\KYC\RejectKYCAction;
use App\Actions\KYC\SubmitKYCAction;
use App\Models\KYCVerification;
use App\Models\User;
use App\Repositories\KYC\KYCRepository;

class KYCService
{
    public function __construct(
        private SubmitKYCAction  $submitAction,
        private ApproveKYCAction $approveAction,
        private RejectKYCAction  $rejectAction,
        private KYCRepository    $repo,
    ) {}

    public function submit(User $user, array $data, array $files): KYCVerification
    {
        return $this->submitAction->execute($user, $data, $files);
    }

    public function approve(KYCVerification $kyc, User $admin): KYCVerification
    {
        return $this->approveAction->execute($kyc, $admin);
    }

    public function reject(KYCVerification $kyc, User $admin, string $reason): KYCVerification
    {
        return $this->rejectAction->execute($kyc, $admin, $reason);
    }

    public function forAdmin(int $perPage = 20, array $filters = [])
    {
        return $this->repo->allForAdmin($perPage, $filters);
    }

    public function pendingCount(): int
    {
        return $this->repo->pendingCount();
    }
}
