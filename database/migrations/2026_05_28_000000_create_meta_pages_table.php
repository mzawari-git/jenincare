<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meta_pages')) {
            return;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_pages');
    }
};
