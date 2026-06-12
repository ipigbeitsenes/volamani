<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // author (vendor staff)
            $table->string('type')->default('note');          // InteractionType
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('pinned')->default(false);
            $table->timestamp('due_at')->nullable();           // for tasks
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'pinned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_interactions');
    }
};
