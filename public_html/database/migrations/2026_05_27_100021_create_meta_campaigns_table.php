<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_account_id')->nullable();
            $table->string('campaign_id', 100)->index()->comment('Facebook Campaign ID');
            $table->string('name');
            $table->string('objective', 50)->comment('OUTCOME_TRAFFIC, OUTCOME_ENGAGEMENT, OUTCOME_SALES, etc.');
            $table->string('status', 30)->default('PAUSED')->comment('ACTIVE, PAUSED, DELETED, ARCHIVED');
            $table->string('buying_type', 30)->default('AUCTION');
            $table->decimal('daily_budget', 12, 2)->nullable();
            $table->decimal('lifetime_budget', 12, 2)->nullable();
            $table->string('bid_strategy', 50)->default('LOWEST_COST_WITHOUT_CAP');
            $table->string('special_ad_categories', 50)->nullable()->comment('CREDIT, EMPLOYMENT, HOUSING, etc.');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('stop_time')->nullable();
            $table->json('insights')->nullable()->comment('Cached performance metrics');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('ad_account_id')->references('id')->on('meta_ad_accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_campaigns');
    }
};
