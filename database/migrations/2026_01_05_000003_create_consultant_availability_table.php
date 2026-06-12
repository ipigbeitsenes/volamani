<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('consultant_profiles')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['profile_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_availability');
    }
};
