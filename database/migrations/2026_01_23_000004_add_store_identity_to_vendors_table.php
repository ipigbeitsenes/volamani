<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // WHO is selling (individual/business/agency/expert/manufacturer)
            $table->string('store_type', 20)->default('individual')->after('category');
            // WHAT the store sells (physical/digital/service/hybrid) — drives the
            // catalog tools and category trees a vendor sees.
            $table->string('store_focus', 20)->default('digital')->after('store_type');

            $table->index('store_type');
            $table->index('store_focus');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['store_type', 'store_focus']);
        });
    }
};
