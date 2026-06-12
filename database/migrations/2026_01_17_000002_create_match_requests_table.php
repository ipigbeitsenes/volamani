<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();          // MTR-...
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('looking_for')->default('vendor'); // MatchTargetType
            $table->string('title');
            $table->text('description');
            $table->string('category')->nullable();
            $table->unsignedBigInteger('budget_min')->nullable();  // kobo
            $table->unsignedBigInteger('budget_max')->nullable();
            $table->string('preferred_location')->nullable();
            $table->boolean('remote_ok')->default(true);
            $table->json('skills')->nullable();
            $table->string('timeline')->nullable();
            $table->string('status')->default('open');       // MatchRequestStatus
            $table->unsignedInteger('matches_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_requests');
    }
};
