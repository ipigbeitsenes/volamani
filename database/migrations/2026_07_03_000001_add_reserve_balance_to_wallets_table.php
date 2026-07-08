<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Non-spendable rolling chargeback reserve, held back from escrow
        // releases and paid out after a safe window. Mirrors escrow_balance.
        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('reserve_balance')->default(0)->after('escrow_balance');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('reserve_balance');
        });
    }
};
