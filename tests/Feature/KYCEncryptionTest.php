<?php

namespace Tests\Feature;

use App\Enums\KYCDocumentType;
use App\Enums\KYCStatus;
use App\Enums\KYCType;
use App\Models\KYCVerification;
use App\Models\User;
use App\Services\Storage\PrivateFileVault;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KYCEncryptionTest extends TestCase
{
    use RefreshDatabase;

    private function makeKyc(array $overrides = []): KYCVerification
    {
        return KYCVerification::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'type' => KYCType::Individual,
            'status' => KYCStatus::Pending,
            'full_name' => 'Ada Lovelace',
            'id_type' => KYCDocumentType::NIN,
            'id_number' => 'A1234567890',
            'date_of_birth' => '1990-05-17',
            'address' => '12 Marina Road, Lagos',
        ], $overrides));
    }

    public function test_pii_columns_are_ciphertext_at_rest_but_transparent_to_the_app(): void
    {
        $kyc = $this->makeKyc();

        // The app sees plaintext through the model.
        $this->assertSame('Ada Lovelace', $kyc->full_name);
        $this->assertSame('A1234567890', $kyc->id_number);
        $this->assertSame('12 Marina Road, Lagos', $kyc->address);

        // The database holds ciphertext, not the plaintext.
        $raw = DB::table('kyc_verifications')->where('id', $kyc->id)->first();
        $this->assertNotSame('A1234567890', $raw->id_number);
        $this->assertNotSame('Ada Lovelace', $raw->full_name);
        $this->assertStringNotContainsString('Marina', $raw->address);

        // ...and it is genuinely our encryption, decrypting back to the original.
        $this->assertSame('A1234567890', Crypt::decryptString($raw->id_number));
        $this->assertSame('Ada Lovelace', Crypt::decryptString($raw->full_name));
    }

    public function test_encrypted_date_of_birth_round_trips_as_a_carbon_instance(): void
    {
        $kyc = $this->makeKyc(['date_of_birth' => '1990-05-17']);

        $this->assertInstanceOf(Carbon::class, $kyc->date_of_birth);
        $this->assertSame('1990-05-17', $kyc->date_of_birth->format('Y-m-d'));

        // Stored ciphertext, not the raw date.
        $raw = DB::table('kyc_verifications')->where('id', $kyc->id)->value('date_of_birth');
        $this->assertNotSame('1990-05-17', $raw);
        $this->assertSame('1990-05-17', Crypt::decryptString($raw));
    }

    public function test_documents_are_encrypted_on_disk_and_decrypted_only_when_streamed(): void
    {
        Storage::fake('private');
        $vault = new PrivateFileVault('private');

        $file = UploadedFile::fake()->image('id-card.jpg', 20, 20);
        $original = $file->getContent();

        $path = $vault->store($file, 'kyc/1');

        // On disk the bytes are ciphertext, never the original image.
        $stored = Storage::disk('private')->get($path);
        $this->assertNotSame($original, $stored);
        $this->assertSame($original, Crypt::decryptString($stored));

        // The vault decrypts on read for authorised streaming.
        $this->assertSame($original, $vault->get($path));
    }

    public function test_legacy_plaintext_files_are_still_readable_until_backfilled(): void
    {
        Storage::fake('private');
        $vault = new PrivateFileVault('private');

        // Simulate a file written before encryption-at-rest existed.
        Storage::disk('private')->put('kyc/1/legacy.jpg', 'raw-plaintext-bytes');

        $this->assertSame('raw-plaintext-bytes', $vault->get('kyc/1/legacy.jpg'));
    }
}
