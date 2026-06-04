<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_stocks')) {
            return;
        }

        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0); // Reserved for orders
            $table->integer('available_quantity')->virtualAs('quantity - reserved_quantity');
            $table->integer('low_stock_threshold')->default(10);
            $table->enum('stock_status', ['in_stock', 'low_stock', 'out_of_stock'])->virtualAs('CASE WHEN available_quantity <= 0 THEN \'out_of_stock\' WHEN available_quantity <= low_stock_threshold THEN \'low_stock\' ELSE \'in_stock\' END');
            $table->decimal('cost_price', 10, 2)->nullable(); // Warehouse-specific cost
            $table->string('location')->nullable(); // Bin location, shelf, etc.
            $table->json('metadata')->nullable(); // Batch number, expiry date, etc.
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id']);
            $table->index(['warehouse_id', 'stock_status']);
            $table->index(['product_id', 'quantity']);
            $table->index('last_updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};
