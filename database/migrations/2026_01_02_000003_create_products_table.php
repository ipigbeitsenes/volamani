<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();

            // Core
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('type')->default('digital'); // digital, template, ebook, software, ui_kit, course, asset, other

            // Pricing (in kobo)
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('compare_price')->nullable();

            // Media
            $table->string('thumbnail')->nullable();
            $table->string('preview_url')->nullable();

            // Download settings
            $table->boolean('is_downloadable')->default(true);
            $table->unsignedSmallInteger('download_limit')->nullable();
            $table->unsignedSmallInteger('download_expiry_hours')->default(48);

            // Status
            $table->string('status')->default('draft'); // draft, pending, active, rejected, archived
            $table->boolean('is_featured')->default(false);

            // Counters (cached for performance)
            $table->unsignedBigInteger('sales_count')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('reviews_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);

            // SEO
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();

            // Moderation
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('is_featured');
            $table->index('price');
            $table->index('average_rating');
            $table->index('sales_count');
        });

        // Pivot defined here (not in the product_tags migration) so the
        // products table it references already exists.
        Schema::create('product_tag', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_tag_id')->constrained('product_tags')->cascadeOnDelete();
            $table->primary(['product_id', 'product_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('products');
    }
};
