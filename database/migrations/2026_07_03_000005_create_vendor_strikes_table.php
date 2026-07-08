<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Audit trail of every strike issued against a vendor. A strike is
        // "active" while cleared_at is null; the vendors.strikes counter tracks
        // the active total.
        Schema::create('vendor_strikes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('reason');                              // StrikeReason
            $table->string('source')->default('manual');          // dispute / chargeback / manual
            $table->unsignedBigInteger('source_id')->nullable();  // dispute/chargeback id
            $table->text('note')->nullable();

            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('cleared_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_strikes');
    }
};
