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
            $table->string('app_name_ar')->default('Jenin Care - SkinAnalyzer');
            $table->string('app_name_en')->default('Jenin Care - SkinAnalyzer');
            $table->string('primary_color')->default('#1A73E8');
            $table->string('accent_color')->default('#34A853');
            $table->string('background_color')->default('#FFFFFF');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('server_url')->nullable();
            $table->string('privacy_policy_url')->nullable();
            $table->string('app_store_url')->nullable();
            $table->string('google_play_url')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_twitter')->nullable();
            $table->text('welcome_message_ar')->nullable();
            $table->text('welcome_message_en')->nullable();
            $table->boolean('is_customized')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('white_label_settings');
    }
};
