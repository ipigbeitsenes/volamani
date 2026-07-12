<?php

namespace App\Actions\Consultations;

use App\Enums\ConsultationSessionStatus;
use App\Models\ConsultationSession;
use App\Services\Escrow\EscrowService;
use Illuminate\Support\Facades\DB;

class CompleteSessionAction
{
    public function __construct(private EscrowService $escrowService) {}

    public function execute(ConsultationSession $session, ?string $consultantNotes = null): ConsultationSession
    {
        abort_unless($session->canBeCompleted(), 422, 'Session cannot be marked complete at this stage.');

        return DB::transaction(function () use ($session, $consultantNotes) {
            $session->update([
                'status' => ConsultationSessionStatus::Completed,
                'completed_at' => now(),
                'consultant_notes' => $consultantNotes,
            ]);

            $session->profile->increment('total_sessions');

            // Session delivered — release held funds to the consultant.
            $this->escrowService->releaseForPayable($session);

            return $session->fresh();
        });
    }
}
