<?php

namespace App\Actions\Consultations;

use App\Enums\ConsultationSessionStatus;
use App\Models\ConsultationSession;

class ConfirmSessionAction
{
    public function execute(ConsultationSession $session, string $meetingLink, ?string $platform = null): ConsultationSession
    {
        abort_unless($session->canBeConfirmed(), 422, 'Session cannot be confirmed at this stage.');

        $session->update([
            'status' => ConsultationSessionStatus::Confirmed,
            'meeting_link' => $meetingLink,
            'meeting_platform' => $platform ?? $session->meeting_platform,
            'confirmed_at' => now(),
        ]);

        return $session->fresh();
    }
}
