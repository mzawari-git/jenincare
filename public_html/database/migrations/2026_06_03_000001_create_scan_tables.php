<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skin_scans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, in_review, approved, rejected
            $table->string('image_url')->nullable();
            $table->float('overall_health_score')->default(0);
            $table->float('hydration')->default(0);
            $table->float('sebum')->default(0);
            $table->float('pigmentation')->default(0);
            $table->float('pores')->default(0);
            $table->float('elasticity')->default(0);
            $table->text('custom_arabic_analysis')->nullable();
            $table->json('expert_free_tips')->nullable();
            $table->boolean('is_locked')->default(true);
            $table->string('pin_code', 6)->nullable();
            $table->integer('pin_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->float('lighting_quality')->nullable();
            $table->float('face_confidence')->nullable();
            $table->integer('image_width')->nullable();
            $table->integer('image_height')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('scan_heatmap_points', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('scan_id')->constrained('skin_scans')->cascadeOnDelete();
            $table->float('x');
            $table->float('y');
            $table->float('severity');
            $table->string('label')->nullable();
            $table->string('label_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->timestamps();
        });

        Schema::create('scan_defects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('scan_id')->constrained('skin_scans')->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en');
            $table->float('severity');
            $table->text('tip_ar')->nullable();
            $table->text('tip_en')->nullable();
            $table->string('icon_name')->nullable();
            $table->timestamps();
        });

        Schema::create('scan_defect_products', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('defect_id')->constrained('scan_defects')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->text('matching_reason')->nullable();
            $table->text('matching_reason_ar')->nullable();
            $table->timestamps();
        });

        Schema::create('scan_timeline_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('scan_id')->constrained('skin_scans')->cascadeOnDelete();
            $table->string('status');
            $table->string('description');
            $table->string('description_ar')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_id');
            $table->string('platform')->default('android');
            $table->string('device_model')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('fcm_token')->nullable();
            $table->timestamps();

            $table->unique(['device_id', 'platform']);
        });

        Schema::create('scan_general_tips', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('scan_id')->constrained('skin_scans')->cascadeOnDelete();
            $table->text('tip_ar')->nullable();
            $table->text('tip_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_general_tips');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('scan_timeline_events');
        Schema::dropIfExists('scan_defect_products');
        Schema::dropIfExists('scan_defects');
        Schema::dropIfExists('scan_heatmap_points');
        Schema::dropIfExists('skin_scans');
    }
};
