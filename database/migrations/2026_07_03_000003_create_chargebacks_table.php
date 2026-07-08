<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A payment-gateway chargeback (Paystack calls these "disputes"). Opened
        // from a charge.dispute.create webhook or manually by an admin. Freezes
        // the linked escrow if still held, otherwise claws back the vendor's
        // earnings (reserve first, then spendable balance).
        Schema::create('chargebacks', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();               // CBK-...
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('escrow_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();

            $table->string('gateway_reference')->nullable();     // gateway dispute/txn ref
            $table->unsignedBigInteger('amount');                // kobo disputed
            $table->unsignedBigInteger('clawed_back_amount')->default(0); // recovered from vendor
            $table->unsignedBigInteger('unrecovered_amount')->default(0); // shortfall (negative-guarded)

            $table->string('reason')->nullable();
            $table->string('status')->default('open');           // ChargebackStatus
            $table->json('evidence')->nullable();                // contest evidence: files + notes

            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('vendor_id');
            $table->index('gateway_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chargebacks');
    }
};
