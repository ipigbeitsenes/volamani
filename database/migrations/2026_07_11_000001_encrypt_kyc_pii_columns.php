<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Encrypt personally-identifiable KYC fields at rest. Ciphertext is far longer
 * than the source, so the columns widen to TEXT; existing plaintext rows are
 * encrypted in place. Kept out of the model layer here (raw DB) so the new
 * casts don't try to decrypt still-plaintext values mid-migration.
 */
return new class extends Migration
{
    /** Sensitive scalar identity fields to encrypt (date_of_birth handled separately). */
    private array $fields = ['full_name', 'id_number', 'address', 'rc_number'];

    public function up(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->text('full_name')->change();
            $table->text('id_number')->change();
            $table->text('date_of_birth')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('rc_number')->nullable()->change();
        });

        DB::table('kyc_verifications')->orderBy('id')->each(function ($row) {
            $updates = [];

            foreach ([...$this->fields, 'date_of_birth'] as $field) {
                $value = $row->{$field} ?? null;

                if ($value === null || $value === '' || $this->isEncrypted($value)) {
                    continue;
                }

                $updates[$field] = Crypt::encryptString((string) $value);
            }

            if ($updates) {
                DB::table('kyc_verifications')->where('id', $row->id)->update($updates);
            }
        });
    }

    public function down(): void
    {
        // Decrypt back to plaintext before narrowing the column types.
        DB::table('kyc_verifications')->orderBy('id')->each(function ($row) {
            $updates = [];

            foreach ([...$this->fields, 'date_of_birth'] as $field) {
                $value = $row->{$field} ?? null;

                if ($value === null || $value === '' || ! $this->isEncrypted($value)) {
                    continue;
                }

                $updates[$field] = Crypt::decryptString($value);
            }

            if ($updates) {
                DB::table('kyc_verifications')->where('id', $row->id)->update($updates);
            }
        });

        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->string('full_name')->change();
            $table->string('id_number')->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('rc_number')->nullable()->change();
        });
    }

    /** True when a value already looks like a Laravel-encrypted payload. */
    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
