<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrow_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escrow_id')->constrained('escrows')->cascadeOnDelete();
            $table->string('reference')->unique();

            // hold / release / refund / dispute
            $table->string('type')->index();
            $table->unsignedBigInteger('amount'); // kobo
            // Amount still held in escrow after this entry
            $table->unsignedBigInteger('balance_after');

            $table->string('description');
            // Who triggered it (buyer/vendor/admin) — null for system (auto-release)
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();

            // Immutable audit row — created only, never updated
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrow_transactions');
    }
};
