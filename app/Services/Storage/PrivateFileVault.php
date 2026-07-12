<?php

namespace App\Services\Storage;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Encryption-at-rest for sensitive private files (KYC identity documents).
 *
 * Files land on the `private` disk as ciphertext, so a leaked disk/bucket, a
 * stray backup, or filesystem access never exposes a customer's ID or selfie.
 * Bytes are encrypted with the app key on write and decrypted only when an
 * authorised controller streams them for review — never via a public URL.
 */
class PrivateFileVault
{
    private const MIME_BY_EXTENSION = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
    ];

    public function __construct(private string $disk = 'private') {}

    /** Encrypt and store an uploaded file; returns the stored path. */
    public function store(UploadedFile $file, string $dir): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $path = trim($dir, '/').'/'.Str::random(40).'.'.$extension;

        Storage::disk($this->disk)->put($path, Crypt::encryptString($file->get()));

        return $path;
    }

    /** Stream a stored file back to the browser, decrypting it on the fly. */
    public function stream(string $path, bool $inline = true): StreamedResponse
    {
        $plaintext = $this->get($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = self::MIME_BY_EXTENSION[$extension] ?? 'application/octet-stream';
        $disposition = $inline ? 'inline' : 'attachment';

        return response()->streamDownload(
            function () use ($plaintext) {
                echo $plaintext;
            },
            basename($path),
            ['Content-Type' => $mime],
            $disposition,
        );
    }

    /**
     * Decrypt and return a stored file's contents. Falls back to the raw bytes
     * for files written before encryption was introduced, so existing documents
     * keep working until the backfill (kyc:encrypt-documents) runs.
     */
    public function get(string $path): string
    {
        $raw = Storage::disk($this->disk)->get($path);

        try {
            return Crypt::decryptString($raw);
        } catch (DecryptException) {
            return $raw;
        }
    }

    public function exists(?string $path): bool
    {
        return $path !== null && $path !== '' && Storage::disk($this->disk)->exists($path);
    }

    public function delete(?string $path): void
    {
        if ($this->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }
}
