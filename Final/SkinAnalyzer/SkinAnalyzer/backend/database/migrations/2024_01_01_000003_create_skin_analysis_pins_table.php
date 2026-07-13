<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skin_analysis_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skin_analysis_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('pin_code', 4);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['pin_code', 'is_used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skin_analysis_pins');
    }
};
