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
            $table->string('name');
            $table->string('provider_key');
            $table->text('system_instruction');
            $table->enum('tone', ['medical', 'promotional', 'balanced'])->default('balanced');
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_prompts');
    }
};
