<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ad_sets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('ad_account_id')->nullable();
            $table->string('ad_set_id', 100)->index()->comment('Facebook Ad Set ID');
            $table->string('name');
            $table->string('status', 30)->default('PAUSED');
            $table->string('optimization_goal', 50)->default('IMPRESSIONS');
            $table->string('billing_event', 50)->default('IMPRESSIONS');
            $table->decimal('daily_budget', 12, 2)->nullable();
            $table->decimal('lifetime_budget', 12, 2)->nullable();
            $table->decimal('bid_amount', 10, 2)->nullable();
            $table->json('targeting')->nullable()->comment('Full targeting spec: geo, age, gender, interests, behaviors, custom_audiences');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('promoted_object', 100)->nullable()->comment('page_id, product_set_id, etc.');
            $table->json('insights')->nullable()->comment('Cached performance metrics');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('meta_campaigns')->onDelete('cascade');
            $table->foreign('ad_account_id')->references('id')->on('meta_ad_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ad_sets');
    }
};
