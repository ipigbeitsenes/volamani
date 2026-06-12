<?php

namespace App\Services\Consultations;

use App\Actions\Consultations\BookConsultationAction;
use App\Actions\Consultations\CancelSessionAction;
use App\Actions\Consultations\CompleteSessionAction;
use App\Actions\Consultations\ConfirmSessionAction;
use App\Actions\Consultations\CreateConsultantProfileAction;
use App\Actions\Consultations\CreatePackageAction;
use App\Actions\Consultations\UpdateConsultantProfileAction;
use App\Models\ConsultantProfile;
use App\Models\ConsultationPackage;
use App\Models\ConsultationSession;
use App\Models\User;
use App\Models\Vendor;

class ConsultationService
{
    public function __construct(
        private CreateConsultantProfileAction $createProfile,
        private UpdateConsultantProfileAction $updateProfile,
        private BookConsultationAction        $book,
        private ConfirmSessionAction          $confirm,
        private CompleteSessionAction         $complete,
        private CancelSessionAction           $cancel,
        private CreatePackageAction           $createPackage,
    ) {}

    public function createProfile(Vendor $vendor, array $data): ConsultantProfile
    {
        return $this->createProfile->execute($vendor, $data);
    }

    public function updateProfile(ConsultantProfile $profile, array $data): ConsultantProfile
    {
        return $this->updateProfile->execute($profile, $data);
    }

    public function bookSession(ConsultantProfile $profile, ConsultationPackage $package, User $buyer, array $data): ConsultationSession
    {
        return $this->book->execute($profile, $package, $buyer, $data);
    }

    public function confirmSession(ConsultationSession $session, string $meetingLink, ?string $platform = null): ConsultationSession
    {
        return $this->confirm->execute($session, $meetingLink, $platform);
    }

    public function completeSession(ConsultationSession $session, ?string $consultantNotes = null): ConsultationSession
    {
        return $this->complete->execute($session, $consultantNotes);
    }

    public function cancelSession(ConsultationSession $session, string $reason, bool $byConsultant = false): ConsultationSession
    {
        return $this->cancel->execute($session, $reason, $byConsultant);
    }

    public function addPackage(ConsultantProfile $profile, array $data): ConsultationPackage
    {
        return $this->createPackage->execute($profile, $data);
    }

    public function togglePackage(ConsultationPackage $package): ConsultationPackage
    {
        $package->update(['is_active' => !$package->is_active]);
        return $package->fresh();
    }

    public function deletePackage(ConsultationPackage $package): void
    {
        abort_if(
            $package->sessions()->whereNotIn('status', ['cancelled', 'completed'])->exists(),
            422,
            'Cannot delete a package with active sessions.'
        );
        $package->delete();
    }

    public function toggleAvailability(ConsultantProfile $profile): ConsultantProfile
    {
        $profile->update(['is_available' => !$profile->is_available]);
        return $profile->fresh();
    }
}
