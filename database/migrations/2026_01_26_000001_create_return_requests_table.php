<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Structured returns / RMA for PHYSICAL orders: request → approve →
        // ship back → confirm receipt → refund (escrow). Funds stay held in
        // escrow throughout (the request freezes auto-release).
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();           // RET-...
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            $table->string('reason');                         // ReturnReason
            $table->text('description')->nullable();
            $table->json('photos')->nullable();               // public-disk paths
            $table->string('status')->default('requested');   // ReturnStatus

            $table->string('return_tracking', 120)->nullable();
            $table->text('decision_note')->nullable();        // seller/admin note on approve/reject
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('refunded_amount')->nullable(); // kobo, set on refund

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('shipped_back_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('vendor_id');
            $table->index('buyer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
