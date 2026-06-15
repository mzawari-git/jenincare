<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'show_in_b2c', 'published_at'], 'idx_products_status_b2c_published');
            $table->index('sales_count', 'idx_products_sales_count');
        });

        Schema::table('pos_sales', function (Blueprint $table) {
            $table->index('store_id', 'idx_pos_sales_store_id');
            $table->index('customer_email', 'idx_pos_sales_customer_email');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->index('user_id', 'idx_devices_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_status_b2c_published');
            $table->dropIndex('idx_products_sales_count');
        });

        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropIndex('idx_pos_sales_store_id');
            $table->dropIndex('idx_pos_sales_customer_email');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropIndex('idx_devices_user_id');
        });
    }
};
