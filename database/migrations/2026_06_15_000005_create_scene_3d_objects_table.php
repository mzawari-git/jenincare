<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scene_3d_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained('store_scenes')->cascadeOnDelete();
            $table->string('model_path')->nullable();
            $table->string('object_type')->default('product_display');
            $table->float('position_x')->default(0);
            $table->float('position_y')->default(0);
            $table->float('position_z')->default(0);
            $table->float('rotation_x')->default(0);
            $table->float('rotation_y')->default(0);
            $table->float('rotation_z')->default(0);
            $table->float('scale')->default(1);
            $table->string('color')->nullable();
            $table->boolean('is_walkable')->default(true);
            $table->boolean('is_collision')->default(false);
            $table->string('label_ar')->nullable();
            $table->string('label_en')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('store_scenes', function (Blueprint $table) {
            $table->boolean('3d_enabled')->default(false)->after('is_active');
            $table->string('ground_plane_url')->nullable()->after('video_path');
            $table->string('skybox_url')->nullable()->after('ground_plane_url');
        });
    }

    public function down(): void
    {
        Schema::table('store_scenes', function (Blueprint $table) {
            $table->dropColumn(['3d_enabled', 'ground_plane_url', 'skybox_url']);
        });
        Schema::dropIfExists('scene_3d_objects');
    }
};
