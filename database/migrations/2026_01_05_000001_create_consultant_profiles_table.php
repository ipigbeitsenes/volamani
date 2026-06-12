<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('display_name');
            $table->text('bio');
            $table->string('niche')->nullable();
            $table->json('expertise')->nullable();
            $table->unsignedTinyInteger('experience_years')->default(1);
            $table->string('linkedin')->nullable();
            $table->string('calendly_url')->nullable();
            $table->boolean('is_available')->default(true);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('total_sessions')->default(0);
            $table->timestamps();

            $table->index(['is_available', 'average_rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_profiles');
    }
};
