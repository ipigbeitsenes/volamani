<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypts a date at rest while exposing it as a Carbon instance to the app.
 *
 * Laravel ships an `encrypted` cast but no `encrypted:date`, and a plain
 * `encrypted` cast on a DATE column would hand views a ciphertext string instead
 * of the Carbon they format. This stores the encrypted 'Y-m-d' in a text column
 * and rehydrates it as Carbon on read.
 *
 * @implements CastsAttributes<Carbon|null, Carbon|string|null>
 */
class EncryptedDate implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse(Crypt::decryptString($value))->startOfDay();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof \DateTimeInterface
            ? Carbon::instance($value)
            : Carbon::parse($value);

        return Crypt::encryptString($date->format('Y-m-d'));
    }
}
