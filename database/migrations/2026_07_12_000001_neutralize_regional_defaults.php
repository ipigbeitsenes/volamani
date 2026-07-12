<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * De-regionalize static column defaults: currency falls back to USD instead of
 * NGN, and KYC no longer assumes Nigeria as the country. The app sets these
 * explicitly (currency_code()) on new records; this only changes the DB-level
 * fallback and does not touch existing rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['payments', 'wallets', 'orders', 'documents'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('currency', 3)->default('USD')->change();
            });
        }

        Schema::table('kyc_verifications', function (Blueprint $t) {
            $t->string('country')->default('')->change();
        });
    }

    public function down(): void
    {
        foreach (['payments', 'wallets', 'orders', 'documents'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('currency', 3)->default('NGN')->change();
            });
        }

        Schema::table('kyc_verifications', function (Blueprint $t) {
            $t->string('country')->default('Nigeria')->change();
        });
    }
};
