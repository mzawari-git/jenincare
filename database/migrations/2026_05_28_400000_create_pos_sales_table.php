<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->string('pos_sale_id', 100)->unique()->index();
            $table->uuid('uuid')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('store_id', 100)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->decimal('order_total', 12, 2);
            $table->decimal('subtotal', 12, 2)->nullable();
            $table->string('currency', 3)->default('ILS');
            $table->json('items')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->boolean('matched_to_online')->default(false);
            $table->timestamp('sale_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sales');
    }
};
