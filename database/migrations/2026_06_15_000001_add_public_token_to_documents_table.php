<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Unguessable handle for the public, no-login share link sent to clients.
            $table->string('public_token', 64)->nullable()->unique()->after('number');
        });

        // Backfill tokens for any documents created before this column existed.
        DB::table('documents')->whereNull('public_token')->orderBy('id')
            ->each(function ($doc) {
                DB::table('documents')->where('id', $doc->id)
                    ->update(['public_token' => Str::random(40)]);
            });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn('public_token');
        });
    }
};
