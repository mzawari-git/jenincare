<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deduplication_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_name', 100)->index();
            $table->string('event_id', 100);
            $table->string('key_type', 50)->default('primary');
            $table->boolean('blocked')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['event_name', 'created_at']);
            $table->index(['event_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deduplication_logs');
    }
};
