<?php

namespace App\Actions\Consultations;

use App\Enums\ConsultationSessionStatus;
use App\Models\ConsultationSession;

class CancelSessionAction
{
    public function execute(ConsultationSession $session, string $reason, bool $byConsultant = false): ConsultationSession
    {
        abort_unless($session->canBeCancelled(), 422, 'Session cannot be cancelled at this stage.');

        $session->update([
            'status' => ConsultationSessionStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $session->fresh();
    }
}
