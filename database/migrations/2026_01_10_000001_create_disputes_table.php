<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();

            // The escrow whose funds are frozen by this dispute.
            $table->foreignId('escrow_id')->constrained('escrows')->cascadeOnDelete();

            // Denormalised parties (pulled from the escrow) for fast lookups.
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('raised_by')->constrained('users');     // opener (buyer or vendor user)

            $table->string('reason');                                 // DisputeReason
            $table->text('description');

            $table->string('status')->default('open')->index();       // DisputeStatus

            // Filled on resolution.
            $table->string('resolution')->nullable();                 // DisputeResolution
            $table->unsignedBigInteger('resolution_amount')->nullable(); // kobo (vendor share on split)
            $table->text('resolution_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
