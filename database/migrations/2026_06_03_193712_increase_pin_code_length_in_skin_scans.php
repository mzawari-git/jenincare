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
        Schema::table('skin_scans', function (Blueprint $table) {
            $table->string('pin_code', 16)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('skin_scans', function (Blueprint $table) {
            $table->string('pin_code', 6)->nullable()->change();
        });
    }
};
