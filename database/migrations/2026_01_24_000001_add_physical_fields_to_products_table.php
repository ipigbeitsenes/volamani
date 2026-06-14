<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Fulfillment discriminator. All existing rows are digital.
            $table->string('kind', 20)->default('digital')->after('vendor_id');
            // PRIMARY category for physical products (digital keeps category_id).
            $table->foreignId('physical_category_id')->nullable()->after('category_id')
                ->constrained('physical_categories')->nullOnDelete();

            $table->index('kind');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('physical_category_id');
            $table->dropColumn('kind');
        });
    }
};
