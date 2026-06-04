<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('white_label_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('clinic_name')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('primary_color')->default('#0D7CFF');
            $table->string('accent_color')->default('#00BFA5');
            $table->string('theme_mode')->default('dark');
            $table->string('style_preset')->default('medicore');
            $table->string('app_title')->nullable();
            $table->json('fonts')->nullable();
            $table->json('extra_css')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('white_label_settings');
    }
};
