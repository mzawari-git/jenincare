<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'site_theme')
            ->update(['value' => json_encode('rose')]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'site_theme')
            ->update(['value' => json_encode('minimal')]);
    }
};
