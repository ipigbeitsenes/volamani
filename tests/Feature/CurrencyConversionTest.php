<?php

namespace Tests\Feature;

use App\Services\Currency\CurrencyService;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurrencySeeder::class);   // base NGN, USD @ 1550
    }

    public function test_converts_vendor_currency_to_base_and_back(): void
    {
        $svc = app(CurrencyService::class);

        // $500.00 (50,000 cents) at ₦1,550 = ₦775,000.00 (77,500,000 kobo)
        $this->assertSame(77_500_000, $svc->toBase(50_000, 'USD'));
        $this->assertSame(50_000, $svc->fromBase(77_500_000, 'USD'));
    }

    public function test_base_currency_passes_through_unchanged(): void
    {
        $svc = app(CurrencyService::class);

        $this->assertSame('NGN', $svc->base());
        $this->assertSame(500_000, $svc->toBase(500_000, 'NGN'));
        $this->assertSame(500_000, $svc->fromBase(500_000, 'NGN'));
    }

    public function test_formats_amounts_with_the_right_symbol(): void
    {
        $svc = app(CurrencyService::class);

        $this->assertSame('$500.00', $svc->format(50_000, 'USD'));
        // A base (₦775,000) product shown to its USD vendor reads as $500.00
        $this->assertSame('$500.00', $svc->displayFromBase(77_500_000, 'USD'));
    }
}
