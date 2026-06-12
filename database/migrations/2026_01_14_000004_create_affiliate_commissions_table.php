<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();           // AFC-...
            $table->foreignId('affiliate_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('earnable');              // Order / ServiceOrder / ConsultationSession that triggered it
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');                          // CommissionType: signup_bonus | sale_commission
            $table->unsignedBigInteger('amount');            // kobo
            $table->decimal('rate_applied', 5, 2)->nullable(); // % used for sale commissions
            $table->string('status')->default('pending');    // CommissionStatus
            $table->unsignedBigInteger('wallet_ledger_id')->nullable(); // set when paid out
            $table->string('note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_account_id', 'status']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
