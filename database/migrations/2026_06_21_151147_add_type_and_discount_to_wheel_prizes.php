<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wheel_prizes', function (Blueprint $table) {
            $table->string('type', 20)->default('product')->after('name');
            $table->unsignedInteger('discount_percent')->nullable()->after('image');
            $table->string('value', 255)->nullable()->after('discount_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wheel_prizes', function (Blueprint $table) {
            $table->dropColumn(['type', 'discount_percent', 'value']);
        });
    }
};
