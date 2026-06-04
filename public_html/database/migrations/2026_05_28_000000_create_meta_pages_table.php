<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('meta_pages')) {
            Schema::create('meta_pages', function (Blueprint $table) {
                $table->id();
                $table->string('page_id')->unique();
                $table->string('page_name');
                $table->string('page_picture_url')->nullable();
                $table->text('access_token')->nullable();
                $table->boolean('webhook_subscribed')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_pages');
    }
};
