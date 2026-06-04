<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1)->index();

            $table->string('order_number')->unique()->index();
            $table->enum('order_type', ['b2c', 'b2b'])->default('b2c')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('customer_phone_secondary')->nullable();

            $table->text('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_region')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('shipping_country')->default('PS');
            $table->text('shipping_notes')->nullable();
            $table->decimal('shipping_latitude', 10, 8)->nullable();
            $table->decimal('shipping_longitude', 11, 8)->nullable();

            $table->boolean('billing_same_as_shipping')->default(true);
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_region')->nullable();

            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_code')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency')->default('ILS');

            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'packaging',
                'ready_for_pickup', 'out_for_delivery', 'delivered',
                'cancelled', 'refunded', 'on_hold'
            ])->default('pending')->index();

            $table->enum('payment_status', [
                'pending', 'paid', 'partially_paid', 'failed', 'refunded', 'cancelled'
            ])->default('pending')->index();

            $table->enum('payment_method', [
                'cod', 'bank_transfer', 'credit_card', 'paypal', 'stripe', 'credit_line'
            ])->default('cod');

            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('paid_amount', 10, 2)->default(0);

            $table->string('courier_service')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('estimated_delivery_days')->default(3);

            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->json('status_history')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            $table->string('source')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('referrer')->nullable();
            $table->string('user_agent')->nullable();
            $table->ipAddress('ip_address')->nullable();

            $table->boolean('meta_capi_sent')->default(false);
            $table->timestamp('meta_capi_sent_at')->nullable();
            $table->json('meta_capi_response')->nullable();

            $table->integer('customer_rating')->nullable();
            $table->text('customer_review')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
            $table->index(['order_type', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('product_id')->index();

            $table->string('product_sku');
            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->string('product_image')->nullable();

            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);

            $table->json('product_attributes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
