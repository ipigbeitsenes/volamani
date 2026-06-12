<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // NOTE: the product_tag pivot lives in the products migration
        // (2026_01_02_000003) because it needs the products table to exist first.
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tags');
    }
};
