<?php

namespace App\Console\Commands;

use App\Models\KYCVerification;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/**
 * One-off backfill: encrypt KYC document files uploaded before encryption-at-rest
 * was introduced. Idempotent — files already encrypted are detected and skipped,
 * so it is safe to run more than once.
 */
class EncryptKycDocuments extends Command
{
    protected $signature = 'kyc:encrypt-documents {--disk=private}';

    protected $description = 'Encrypt any KYC document files still stored as plaintext';

    private const DOCUMENT_FIELDS = ['document_front', 'document_back', 'selfie', 'proof_of_address'];

    public function handle(): int
    {
        $disk = Storage::disk($this->option('disk'));
        $encrypted = 0;
        $skipped = 0;

        KYCVerification::query()->each(function (KYCVerification $kyc) use ($disk, &$encrypted, &$skipped) {
            foreach (self::DOCUMENT_FIELDS as $field) {
                $path = $kyc->getRawOriginal($field);

                if (! $path || ! $disk->exists($path)) {
                    continue;
                }

                $raw = $disk->get($path);

                if ($this->isEncrypted($raw)) {
                    $skipped++;

                    continue;
                }

                $disk->put($path, Crypt::encryptString($raw));
                $encrypted++;
                $this->line("Encrypted {$path}");
            }
        });

        $this->info("Done. Encrypted {$encrypted} file(s), skipped {$skipped} already-encrypted.");

        return self::SUCCESS;
    }

    private function isEncrypted(string $contents): bool
    {
        try {
            Crypt::decryptString($contents);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
}
