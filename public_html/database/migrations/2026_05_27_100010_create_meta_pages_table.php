<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_id', 100)->unique()->comment('Facebook Page ID');
            $table->string('page_name');
            $table->string('page_username')->nullable();
            $table->string('page_category')->nullable();
            $table->string('page_picture_url')->nullable();
            $table->integer('page_followers')->default(0);
            $table->string('page_access_token', 500)->nullable();
            $table->string('app_id', 100)->nullable();
            $table->string('app_secret', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('webhook_subscribed')->default(false);
            $table->json('webhook_fields')->nullable()->comment('Subscribed webhook fields: feed, messages, etc.');
            $table->json('meta_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_pages');
    }
};
