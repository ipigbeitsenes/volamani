<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->json('categories')->nullable();        // categories the vendor wants leads in
            $table->json('skills')->nullable();
            $table->unsignedBigInteger('min_budget')->nullable();  // kobo — smallest project they take
            $table->unsignedBigInteger('max_budget')->nullable();
            $table->boolean('serves_remote')->default(true);
            $table->json('locations')->nullable();         // cities/states served on-site
            $table->boolean('is_accepting')->default(true);
            $table->unsignedInteger('leads_count')->default(0);
            $table->unsignedInteger('connections_count')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_accepting');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_profiles');
    }
};
