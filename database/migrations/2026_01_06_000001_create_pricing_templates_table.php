<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_templates', function (Blueprint $table) {
            $table->id();
            $table->string('category');          // matches PricingCategory enum value
            $table->string('name');              // e.g. "Basic Website", "E-commerce Site"
            $table->string('pricing_type');      // fixed | hourly | milestone
            $table->unsignedBigInteger('base_price')->default(0);       // kobo (fixed baseline or milestone total)
            $table->unsignedBigInteger('hourly_rate')->default(0);      // kobo per hour
            $table->unsignedSmallInteger('min_hours')->default(0);
            $table->unsignedSmallInteger('max_hours')->default(0);
            $table->text('description')->nullable();
            $table->json('features')->nullable();   // array of included items
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_templates');
    }
};
