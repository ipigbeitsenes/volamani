<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Products can be sold now ('available' — has a file, normal checkout) or
 * pre-sold ('coming_soon' — no file yet, buyers reserve it with a deposit and
 * pay the balance when it's delivered).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('availability', 20)->default('available')->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('availability');
        });
    }
};
