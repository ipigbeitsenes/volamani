<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Optional per-product variants (e.g. Size: L / Colour: Black). When a
        // physical product has variants, stock is tracked per variant.
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');                                  // e.g. "Large / Black"
            $table->string('sku', 80)->nullable();
            $table->unsignedBigInteger('price_override')->nullable(); // kobo; null = use product price
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->json('attributes')->nullable();                  // {"Size":"L","Colour":"Black"}
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
