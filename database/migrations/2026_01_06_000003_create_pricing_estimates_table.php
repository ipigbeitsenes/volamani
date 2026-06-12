<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_estimates', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_token')->nullable()->index();  // for guest saves
            $table->foreignId('template_id')->nullable()->constrained('pricing_templates')->nullOnDelete();

            $table->string('category');
            $table->string('service_name');
            $table->string('pricing_type');
            $table->string('urgency')->default('normal');     // normal|soon|urgent|rush
            $table->decimal('urgency_multiplier', 4, 2)->default(1.00);

            $table->unsignedBigInteger('base_price')->default(0);    // kobo
            $table->unsignedBigInteger('hourly_rate')->default(0);   // kobo
            $table->decimal('estimated_hours', 6, 1)->default(0);

            $table->json('add_ons')->nullable();        // [{id,name,price,is_percentage}]
            $table->unsignedBigInteger('add_ons_total')->default(0); // kobo
            $table->json('milestones')->nullable();     // [{name,description,amount}]

            $table->unsignedBigInteger('subtotal')->default(0);     // kobo (before urgency)
            $table->unsignedBigInteger('total')->default(0);        // kobo (after urgency)

            $table->text('notes')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_estimates');
    }
};
