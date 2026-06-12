<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // What is being paid for
            $table->nullableMorphs('payable');

            $table->string('gateway')->default('paystack');       // paystack|bank_transfer|wallet
            $table->string('gateway_reference')->nullable();      // Paystack's own reference
            $table->string('status')->default('pending');         // PaymentStatus enum
            $table->string('currency', 3)->default('NGN');
            $table->unsignedBigInteger('amount');                 // kobo — total charged

            $table->json('metadata')->nullable();                 // full gateway response
            $table->string('ip_address', 45)->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->unsignedBigInteger('refund_amount')->default(0);
            $table->string('refund_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['gateway_reference']);
            // nullableMorphs('payable') already indexes (payable_type, payable_id).
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
