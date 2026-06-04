<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trigger_words', function (Blueprint $table) {
            $table->id();
            $table->string('word', 255)->index();
            $table->string('category', 100)->index()->nullable();
            $table->string('severity', 20)->default('medium');
            $table->string('platform', 50)->nullable()->index();
            $table->string('action', 20)->default('replace');
            $table->string('replacement', 255)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();

            $table->unique(['word', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trigger_words');
    }
};
