<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('content');
            $table->text('content_ar')->nullable();
            $table->string('engine_type')->default('generative');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('version')->default(1);
            $table->timestamps();

            $table->index('key');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_prompts');
    }
};
