<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Identity
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->string('tagline', 160)->nullable();
            $table->text('description')->nullable();

            // Media
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();

            // Contact & Social
            $table->string('whatsapp', 20)->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable(); // {facebook, twitter, instagram, linkedin, youtube}

            // Location
            $table->string('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('state', 80)->nullable();

            // Classification
            $table->string('category', 80)->nullable();

            // Status & Moderation
            $table->string('status')->default('pending'); // pending, active, suspended, rejected
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            // Verification
            $table->timestamp('verified_at')->nullable();

            // Finance
            $table->unsignedTinyInteger('commission_rate')->nullable(); // override platform default
            $table->string('plan')->default('free'); // free, premium, agency

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('is_featured');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
