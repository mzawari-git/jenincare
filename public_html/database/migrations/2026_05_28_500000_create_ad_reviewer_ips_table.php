<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_reviewer_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->string('user_agent', 500)->nullable();
            $table->string('isp', 255)->nullable();
            $table->string('source', 100)->nullable();
            $table->string('notes')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();

            $table->unique('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_reviewer_ips');
    }
};
