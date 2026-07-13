<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skin_analyses', function (Blueprint $table) {
            $table->index('ai_provider_id');
            $table->index(['user_id', 'status', 'created_at']);
        });

        Schema::table('system_prompts', function (Blueprint $table) {
            $table->index('provider_key');
            $table->index('language');
        });

        Schema::table('white_label_settings', function (Blueprint $table) {
            $table->integer('id')->primary()->first();
        });

        Schema::table('app_settings', function (Blueprint $table) {
            $table->integer('id')->primary()->first();
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->index('tokenable_id');
            $table->index(['tokenable_id', 'tokenable_type']);
        });

        Schema::table('skin_analysis_products', function (Blueprint $table) {
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('skin_analyses', function (Blueprint $table) {
            $table->dropIndex(['ai_provider_id']);
            $table->dropIndex(['user_id', 'status', 'created_at']);
        });

        Schema::table('system_prompts', function (Blueprint $table) {
            $table->dropIndex(['provider_key']);
            $table->dropIndex(['language']);
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropIndex(['tokenable_id']);
            $table->dropIndex(['tokenable_id', 'tokenable_type']);
        });

        Schema::table('skin_analysis_products', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
    }
};
