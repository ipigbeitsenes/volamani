<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1:1 companion to products for PHYSICAL-only attributes. Digital
        // products have no row here.
        Schema::create('product_physical_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();

            // Inventory (used only when the product has no variants).
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);

            // Item attributes
            $table->string('condition', 20)->default('new'); // new | used | refurbished
            $table->string('brand', 120)->nullable();

            // Shipping dimensions (informational in Phase 2; used by shipping in Phase 3)
            $table->unsignedInteger('weight_grams')->nullable();
            $table->unsignedInteger('length_mm')->nullable();
            $table->unsignedInteger('width_mm')->nullable();
            $table->unsignedInteger('height_mm')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_physical_details');
    }
};
