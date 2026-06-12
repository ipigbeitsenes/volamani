<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);   // 0–100
            $table->json('score_breakdown')->nullable();
            $table->string('status')->default('suggested');     // MatchStatus
            $table->boolean('requester_interested')->default(false);
            $table->boolean('vendor_interested')->default(false);
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();

            $table->unique(['match_request_id', 'vendor_id']);
            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_matches');
    }
};
