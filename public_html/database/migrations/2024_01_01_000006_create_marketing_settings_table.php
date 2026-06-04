<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->json('value')->nullable();
            $table->string('group')->default('general')->index();
            $table->string('type')->default('text');
            $table->json('options')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_translatable')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        $defaults = [
            ['key' => 'facebook_pixel_enabled', 'value' => true, 'group' => 'facebook', 'description' => 'تفعيل Facebook Pixel'],
            ['key' => 'facebook_pixel_id', 'value' => null, 'group' => 'facebook', 'description' => 'معرف Facebook Pixel'],
            ['key' => 'facebook_capi_enabled', 'value' => false, 'group' => 'facebook', 'description' => 'تفعيل Facebook Conversions API'],
            ['key' => 'facebook_access_token', 'value' => null, 'group' => 'facebook', 'type' => 'password', 'description' => 'Facebook Access Token'],
            ['key' => 'facebook_test_event_code', 'value' => null, 'group' => 'facebook', 'description' => 'Test Event Code'],

            ['key' => 'tiktok_pixel_enabled', 'value' => true, 'group' => 'tiktok', 'description' => 'تفعيل TikTok Pixel'],
            ['key' => 'tiktok_pixel_id', 'value' => null, 'group' => 'tiktok', 'description' => 'معرف TikTok Pixel'],
            ['key' => 'tiktok_capi_enabled', 'value' => false, 'group' => 'tiktok', 'description' => 'تفعيل TikTok Events API'],
            ['key' => 'tiktok_access_token', 'value' => null, 'group' => 'tiktok', 'type' => 'password', 'description' => 'TikTok Access Token'],

            ['key' => 'tracking_enabled', 'value' => true, 'group' => 'marketing', 'description' => 'تفعيل نظام التتبع'],
            ['key' => 'tracking_test_mode', 'value' => false, 'group' => 'marketing', 'description' => 'وضع الاختبار'],
            ['key' => 'tracking_async_mode', 'value' => true, 'group' => 'marketing', 'description' => 'استخدام Queue للإرسال في الخلفية'],
        ];

        foreach ($defaults as $setting) {
            DB::table('marketing_settings')->insert([
                'key' => $setting['key'],
                'value' => json_encode($setting['value']),
                'group' => $setting['group'],
                'type' => $setting['type'] ?? 'text',
                'description' => $setting['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_settings');
    }
};
