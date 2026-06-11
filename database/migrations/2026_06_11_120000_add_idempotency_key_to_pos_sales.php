<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->nullable()->after('parent_sale_id')->unique();
            $table->index(['status', 'created_at'], 'pos_sales_status_created_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->json('pos_favorites')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropColumn('idempotency_key');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->text('pos_favorites')->nullable()->change();
        });
    }
};
