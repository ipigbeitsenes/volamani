<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // When a paid promotion expires (null = not promoted / open-ended admin feature).
            $table->timestamp('featured_until')->nullable()->after('is_featured');
            $table->index('featured_until');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('featured_until');
        });
    }
};
