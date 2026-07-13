<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ledger of the platform's own commission per order (distinct from the
        // affiliate_commissions table). One row per order — the source of truth
        // for "what does the platform collect, and did we collect it".
        Schema::create('platform_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedBigInteger('amount');            // commission (kobo)
            $table->string('currency', 3)->default('NGN');
            $table->string('status', 20)->default('pending'); // PlatformCommissionStatus
            $table->string('method', 20)->nullable();         // how it was collected (wallet, cash_pod, …)
            $table->string('reason', 160)->nullable();        // why it was owed/waived
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_commissions');
    }
};
