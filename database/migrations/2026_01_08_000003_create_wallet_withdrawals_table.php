<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');                 // requested amount (kobo)
            $table->unsignedBigInteger('fee')->default(0);        // platform fee (kobo)
            $table->unsignedBigInteger('net_amount');             // amount - fee (kobo)
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number', 20);
            $table->string('bank_code', 10)->nullable();          // Paystack bank code for transfers
            $table->string('status')->default('pending');         // WithdrawalStatus enum
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->string('paystack_transfer_code')->nullable(); // for programmatic payouts
            $table->softDeletes();
            $table->timestamps();

            $table->index(['wallet_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_withdrawals');
    }
};
