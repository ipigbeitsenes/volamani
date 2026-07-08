<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_strikes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason');                 // BuyerStrikeReason
            $table->string('source')->default('manual'); // dispute | chargeback | manual
            $table->unsignedBigInteger('source_id')->nullable(); // dispute/chargeback id
            $table->text('note')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'cleared_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_strikes');
    }
};
