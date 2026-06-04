<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("hero_slides", function (Blueprint $table) {
            $table->string("video_url")->nullable()->after("mobile_image");
            $table->text("html_content")->nullable()->after("video_url");
            $table->string("image_position")->default("right")->after("html_content");
            $table->string("text_color")->default("#262626")->after("image_position");
            $table->string("text_align")->default("right")->after("text_color");
            $table->decimal("overlay_opacity", 3, 2)->default(0.30)->after("text_align");
            $table->string("animation_type")->default("fade")->after("overlay_opacity");
            $table->boolean("parallax")->default(false)->after("animation_type");
            $table->boolean("full_width_image")->default(false)->after("parallax");
            $table->string("content_width")->default("container")->after("full_width_image");
        });
    }

    public function down(): void
    {
        Schema::table("hero_slides", function (Blueprint $table) {
            $table->dropColumn([
                "video_url", "html_content", "image_position", "text_color",
                "text_align", "overlay_opacity", "animation_type", "parallax",
                "full_width_image", "content_width",
            ]);
        });
    }
};
