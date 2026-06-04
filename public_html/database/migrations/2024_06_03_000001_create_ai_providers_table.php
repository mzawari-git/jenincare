<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver_key')->unique();
            $table->enum('engine_type', ['structured', 'generative', 'hybrid']);
            $table->json('api_credentials')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('quota_limit')->default(0);
            $table->unsignedInteger('quota_used')->default(0);
            $table->json('config')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
