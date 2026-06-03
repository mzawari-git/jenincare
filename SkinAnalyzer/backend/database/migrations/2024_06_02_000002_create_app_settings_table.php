<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('login_enabled')->default(true);
            $table->boolean('registration_enabled')->default(true);
            $table->boolean('maintenance_mode')->default(false);
            $table->string('maintenance_message_ar')->nullable();
            $table->string('maintenance_message_en')->nullable();
            $table->string('min_app_version')->nullable();
            $table->string('latest_app_version')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
