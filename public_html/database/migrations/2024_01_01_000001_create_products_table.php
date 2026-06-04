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
            $table->unsignedBigInteger('tenant_id')->default(1)->index();

            $table->string('sku')->unique()->index();
            $table->string('barcode')->unique()->nullable();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->text('short_description_ar')->nullable();

            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('brand_id')->nullable()->index();
            $table->json('tags')->nullable();

            $table->decimal('base_price', 10, 2);
            $table->decimal('b2c_price', 10, 2)->index();
            $table->decimal('b2b_price', 10, 2)->index();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamp('discount_starts_at')->nullable();
            $table->timestamp('discount_ends_at')->nullable();

            $table->integer('b2b_min_quantity')->default(10);
            $table->integer('b2b_tier_1_qty')->default(50);
            $table->decimal('b2b_tier_1_price', 10, 2)->nullable();
            $table->integer('b2b_tier_2_qty')->default(100);
            $table->decimal('b2b_tier_2_price', 10, 2)->nullable();
            $table->integer('b2b_tier_3_qty')->default(200);
            $table->decimal('b2b_tier_3_price', 10, 2)->nullable();

            $table->integer('stock_quantity')->default(0)->index();
            $table->integer('reserved_quantity')->default(0);
            $table->integer('low_stock_alert')->default(10);
            $table->enum('stock_status', ['in_stock', 'low_stock', 'out_of_stock', 'pre_order'])->default('in_stock');
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);

            $table->string('main_image')->nullable();
            $table->string('main_image_webp')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('video_url')->nullable();

            $table->json('specifications')->nullable();
            $table->json('attributes')->nullable();
            $table->string('weight')->nullable();
            $table->json('dimensions')->nullable();

            $table->string('slug')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('og_image')->nullable();

            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('sales_count')->default(0);

            $table->enum('status', ['draft', 'active', 'inactive', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('show_in_b2c')->default(true);
            $table->boolean('show_in_b2b')->default(true);
            $table->timestamp('published_at')->nullable();

            $table->boolean('free_shipping')->default(false);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->integer('estimated_delivery_days')->default(3);

            $table->boolean('compliance_checked')->default(false);
            $table->timestamp('compliance_checked_at')->nullable();
            $table->json('compliance_flags')->nullable();
            $table->text('safety_warnings')->nullable();

            $table->string('barcode_slug')->nullable();
            $table->integer('print_count')->default(0);
            $table->timestamp('last_printed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
