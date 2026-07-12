<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-currency foundation. Vendors price in their own currency; the platform
 * converts to the base currency (settings.currency_code) using these rates. All
 * wallet/escrow/Paystack money stays in the base currency.
 *
 * rate_to_base = value of ONE unit of this currency in base-currency units
 * (base currency itself is 1.0).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();          // ISO 4217, e.g. NGN, USD
            $table->string('name', 60);
            $table->string('symbol', 8);
            $table->decimal('rate_to_base', 20, 6)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('vendors', function (Blueprint $table) {
            // The currency a vendor prices their listings in. Null = base currency.
            $table->string('currency', 3)->nullable()->after('store_focus');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::dropIfExists('currencies');
    }
};
