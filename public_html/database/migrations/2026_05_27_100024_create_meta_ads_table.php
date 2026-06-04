<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_set_id')->nullable();
            $table->unsignedBigInteger('creative_id')->nullable();
            $table->unsignedBigInteger('ad_account_id')->nullable();
            $table->string('ad_id', 100)->nullable()->index()->comment('Facebook Ad ID');
            $table->string('name');
            $table->string('status', 30)->default('PAUSED');
            $table->json('tracking_specs')->nullable();
            $table->json('insights')->nullable()->comment('Cached performance metrics');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('ad_set_id')->references('id')->on('meta_ad_sets')->onDelete('cascade');
            $table->foreign('creative_id')->references('id')->on('meta_ad_creatives')->onDelete('set null');
            $table->foreign('ad_account_id')->references('id')->on('meta_ad_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ads');
    }
};
