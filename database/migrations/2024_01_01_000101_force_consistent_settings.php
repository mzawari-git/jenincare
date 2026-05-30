<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migration.
     * Forces ALL visual settings to guaranteed consistent values
     * so localhost and hosting are 100% identical.
     */
    public function up(): void
    {
        $settings = [
            'site_theme' => 'rose',
            'site_name' => 'شركة جنين للتجميل',
            'site_name_ar' => 'شركة جنين للتجميل',
            'site_name_en' => 'Jenin Care',
            'site_description' => 'متجر الكتروني متخصص في منتجات التجميل والعناية بالبشرة',
            'site_keywords' => 'تجميل, عناية بالبشرة, مكياج, شامبو, صالون',
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')
                ->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => json_encode($value),
                        'group' => 'general',
                        'type' => 'text',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
        }

        // Verify
        $theme = DB::table('settings')->where('key', 'site_theme')->value('value');
        $theme = json_decode($theme) ?? $theme;
        if ($theme !== 'rose') {
            DB::table('settings')->where('key', 'site_theme')->update(['value' => json_encode('rose')]);
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // No reverse — these are idempotent updates
    }
};
