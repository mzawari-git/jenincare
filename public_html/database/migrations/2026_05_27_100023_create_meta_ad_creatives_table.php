<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ad_creatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_account_id')->nullable();
            $table->string('creative_id', 100)->nullable()->index()->comment('Facebook Creative ID after creation');
            $table->string('name');
            $table->string('title', 255)->nullable()->comment('Ad headline (max 40 chars recommended)');
            $table->text('body')->nullable()->comment('Ad primary text');
            $table->string('description', 255)->nullable()->comment('Ad description');
            $table->string('image_hash', 100)->nullable()->comment('Facebook image hash after upload');
            $table->string('image_url', 500)->nullable()->comment('Local or uploaded image URL');
            $table->string('video_id', 100)->nullable();
            $table->string('link_url', 500)->nullable()->comment('Destination URL');
            $table->string('display_link', 500)->nullable();
            $table->string('call_to_action', 50)->nullable()->comment('SHOP_NOW, LEARN_MORE, SIGN_UP, etc.');
            $table->string('page_id', 100)->nullable()->comment('Facebook Page ID for the ad');
            $table->string('instagram_actor_id', 100)->nullable();
            $table->string('product_set_id', 100)->nullable()->comment('For dynamic product ads');
            $table->string('status', 30)->default('draft');
            $table->timestamps();

            $table->foreign('ad_account_id')->references('id')->on('meta_ad_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ad_creatives');
    }
};
