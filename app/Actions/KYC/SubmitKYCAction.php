<?php

namespace App\Actions\KYC;

use App\Enums\KYCStatus;
use App\Models\KYCVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmitKYCAction
{
    /**
     * Submit (or resubmit after rejection) a user's KYC. Documents are stored on
     * the private disk. Sets the user's kyc_status to Pending for admin review.
     */
    public function execute(User $user, array $data, array $files): KYCVerification
    {
        $existing = $user->kycVerification;

        // Note: abort_if() always evaluates its message argument, so the status
        // reference must be guarded behind a real conditional (it's null on first submit).
        if ($existing && in_array($existing->status, [KYCStatus::Pending, KYCStatus::Verified])) {
            abort(422, 'Your identity verification is already ' . $existing->status->label() . '.');
        }

        return DB::transaction(function () use ($user, $data, $files, $existing) {
            $dir = "kyc/{$user->id}";

            $documents = [];
            foreach (['document_front', 'document_back', 'selfie', 'proof_of_address'] as $field) {
                if (!empty($files[$field])) {
                    // Replace any previously stored file for this field.
                    if ($existing && $existing->{$field}) {
                        Storage::disk('private')->delete($existing->{$field});
                    }
                    $documents[$field] = $files[$field]->store($dir, 'private');
                }
            }

            $kyc = KYCVerification::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($data, $documents, [
                    'status'           => KYCStatus::Pending,
                    'rejection_reason' => null,
                    'reviewed_by'      => null,
                    'reviewed_at'      => null,
                    'submitted_at'     => now(),
                ])
            );

            $user->update(['kyc_status' => KYCStatus::Pending]);

            return $kyc->fresh();
        });
    }
}
