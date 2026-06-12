<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('pending_withdrawal')->default(0)->after('escrow_balance');
            $table->string('currency', 3)->default('NGN')->after('pending_withdrawal');
            $table->boolean('is_frozen')->default(false)->after('currency');
            $table->timestamp('last_reconciled_at')->nullable()->after('is_frozen');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['pending_withdrawal', 'currency', 'is_frozen', 'last_reconciled_at']);
        });
    }
};
