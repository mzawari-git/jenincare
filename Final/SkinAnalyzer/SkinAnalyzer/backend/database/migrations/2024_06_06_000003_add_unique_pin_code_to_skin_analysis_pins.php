<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skin_analysis_pins', function (Blueprint $table) {
            $table->unique('pin_code', 'skin_analysis_pins_pin_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('skin_analysis_pins', function (Blueprint $table) {
            $table->dropUnique('skin_analysis_pins_pin_code_unique');
        });
    }
};
