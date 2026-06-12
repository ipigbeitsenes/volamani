<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('consultant_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('type')->default('one_time');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedBigInteger('price');
            $table->unsignedTinyInteger('max_sessions_per_month')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_packages');
    }
};
