<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skin_analysis_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skin_analysis_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('matching_reason')->nullable();
            $table->timestamps();

            $table->unique(['skin_analysis_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skin_analysis_products');
    }
};
