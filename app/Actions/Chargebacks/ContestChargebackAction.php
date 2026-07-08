<?php

namespace App\Actions\Chargebacks;

use App\Enums\ChargebackStatus;
use App\Models\Chargeback;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ContestChargebackAction
{
    /**
     * Attach the vendor's/admin's evidence to a chargeback and mark it contested.
     * Files are stored on the private disk (proof of delivery, correspondence…).
     *
     * @param  UploadedFile[]  $files
     */
    public function execute(Chargeback $chargeback, User $actor, ?string $note, array $files = []): Chargeback
    {
        abort_unless($chargeback->canContest(), 422, 'This chargeback can no longer be contested.');

        $stored = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $stored[] = $file->store("chargebacks/{$chargeback->id}", 'private');
            }
        }

        $evidence = $chargeback->evidence ?? [];
        $evidence['note']       = $note ?: ($evidence['note'] ?? null);
        $evidence['files']      = array_merge($evidence['files'] ?? [], $stored);
        $evidence['contested_by'] = $actor->id;

        $chargeback->update([
            'status'   => ChargebackStatus::Contested,
            'evidence' => $evidence,
        ]);

        return $chargeback->fresh();
    }
}
