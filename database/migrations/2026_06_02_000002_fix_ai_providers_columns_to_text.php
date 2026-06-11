<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->text('api_credentials')->nullable()->change();
            $table->text('config')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->json('api_credentials')->nullable()->change();
            $table->json('config')->nullable()->change();
        });
    }
};
