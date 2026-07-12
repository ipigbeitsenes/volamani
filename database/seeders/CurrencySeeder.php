<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

/**
 * Seeds the supported currencies with approximate rates to the base (NGN).
 * Admins refine the rates from the currency-management screen. Idempotent.
 */
class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        // [code, name, symbol, rate_to_base]  (1 unit of this currency = N base units)
        $currencies = [
            ['NGN', 'Nigerian Naira', '₦', 1],
            ['USD', 'US Dollar', '$', 1550],
            ['GBP', 'British Pound', '£', 1950],
            ['EUR', 'Euro', '€', 1650],
            ['GHS', 'Ghanaian Cedi', 'GH₵', 105],
            ['KES', 'Kenyan Shilling', 'KSh', 12],
            ['ZAR', 'South African Rand', 'R', 85],
            ['CAD', 'Canadian Dollar', 'C$', 1130],
            ['XOF', 'West African CFA', 'CFA', 2.5],
        ];

        foreach ($currencies as [$code, $name, $symbol, $rate]) {
            Currency::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'symbol' => $symbol, 'rate_to_base' => $rate, 'is_active' => true],
            );
        }

        $this->command?->info('Currencies seeded.');
    }
}
