<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_analysis_images', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('scan_id')->constrained('skin_scans')->cascadeOnDelete();
            $table->string('mode')->default('rgb');
            $table->string('image_path');
            $table->json('analysis')->nullable();
            $table->timestamps();

            $table->unique(['scan_id', 'mode']);
            $table->index('mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_analysis_images');
    }
};
