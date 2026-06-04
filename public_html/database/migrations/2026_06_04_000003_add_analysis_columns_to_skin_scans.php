<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skin_scans', function (Blueprint $table) {
            $table->string('analysis_status', 32)->default('pending')->after('status');
            $table->json('analysis_data')->nullable()->after('expert_free_tips');
            $table->json('radar_metrics')->nullable()->after('analysis_data');
            $table->json('advanced_metrics')->nullable()->after('radar_metrics');
            $table->json('defects')->nullable()->after('advanced_metrics');
            $table->json('heatmap_coordinates')->nullable()->after('defects');
            $table->string('image_path')->nullable()->after('image_url');
            $table->string('analyzed_by_provider', 64)->nullable()->after('heatmap_coordinates');
            $table->decimal('confidence_score', 5, 2)->default(0)->after('analyzed_by_provider');
            $table->timestamp('analyzed_at')->nullable()->after('reviewed_at');
            $table->json('recommended_products')->nullable()->after('analyzed_at');
            $table->json('metadata')->nullable()->after('recommended_products');
            $table->index('analysis_status');
        });
    }

    public function down(): void
    {
        Schema::table('skin_scans', function (Blueprint $table) {
            $table->dropColumn([
                'analysis_status',
                'analysis_data',
                'radar_metrics',
                'advanced_metrics',
                'defects',
                'heatmap_coordinates',
                'image_path',
                'analyzed_by_provider',
                'confidence_score',
                'analyzed_at',
                'recommended_products',
                'metadata',
            ]);
        });
    }
};
