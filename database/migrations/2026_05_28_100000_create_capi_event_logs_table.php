<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capi_event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 50)->index();
            $table->string('event_name', 100)->index();
            $table->string('event_id', 100)->index();
            $table->boolean('success')->default(false)->index();
            $table->integer('status_code')->nullable();
            $table->json('response')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->integer('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['platform', 'success']);
            $table->index(['event_name', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capi_event_logs');
    }
};
