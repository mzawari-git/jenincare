<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_audiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meta_page_id')->nullable();
            $table->string('name');
            $table->string('audience_type', 50)->comment('custom, lookalike, engagement, website, video');
            $table->string('meta_audience_id', 100)->nullable()->comment('Facebook Audience ID');
            $table->integer('size')->default(0);
            $table->string('status', 30)->default('draft')->comment('draft, syncing, ready, error');
            $table->json('rules')->nullable()->comment('Audience targeting rules');
            $table->json('meta_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('meta_page_id')->references('id')->on('meta_pages')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_audiences');
    }
};
