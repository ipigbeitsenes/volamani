<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);            // kobo, per billing cycle
            $table->string('billing_interval')->default('monthly');     // BillingInterval
            $table->decimal('commission_rate', 5, 2)->nullable();       // % vendor commission override
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedInteger('max_products')->nullable();        // null = unlimited
            $table->unsignedInteger('max_services')->nullable();
            $table->boolean('featured_listing')->default(false);        // grants featured storefront placement
            $table->json('perks')->nullable();                         // marketing bullet points
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
