<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;

class SignContractAction
{
    /**
     * Record a client's e-signature on a contract of sale. The typed name plus
     * the timestamp and IP form the acceptance record.
     */
    public function execute(Document $contract, string $signedName, ?string $ip = null): Document
    {
        $contract->update([
            'status'      => DocumentStatus::Signed,
            'signed_name' => $signedName,
            'signed_ip'   => $ip,
            'accepted_at' => now(),
        ]);

        return $contract->fresh();
    }
}
