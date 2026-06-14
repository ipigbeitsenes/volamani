<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SECONDARY categories for physical products. The PRIMARY category stays
        // a column on products (added in Phase 2); these are the optional extras.
        Schema::create('physical_category_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('physical_category_id')->constrained('physical_categories')->cascadeOnDelete();
            $table->primary(['product_id', 'physical_category_id'], 'phys_cat_product_primary');
        });

        // SECONDARY categories for freelance services.
        Schema::create('service_category_freelance_service', function (Blueprint $table) {
            $table->foreignId('freelance_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->primary(['freelance_service_id', 'service_category_id'], 'svc_cat_service_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_category_freelance_service');
        Schema::dropIfExists('physical_category_product');
    }
};
