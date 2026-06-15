<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scene_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_scene_id')->constrained('store_scenes')->cascadeOnDelete();
            $table->foreignId('to_scene_id')->constrained('store_scenes')->cascadeOnDelete();
            $table->string('direction')->default('forward');
            $table->string('label_ar')->nullable();
            $table->string('label_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scene_connections');
    }
};
