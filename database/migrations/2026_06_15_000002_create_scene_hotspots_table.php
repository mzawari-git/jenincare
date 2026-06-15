<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scene_hotspots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained('store_scenes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->float('pitch', 8, 2)->default(0);
            $table->float('yaw', 8, 2)->default(0);
            $table->string('label_ar')->nullable();
            $table->string('label_en')->nullable();
            $table->string('icon_type')->default('product');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scene_hotspots');
    }
};
