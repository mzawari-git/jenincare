<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ad_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('ad_account_id', 100)->unique()->comment('Facebook Ad Account ID: act_XXXXXX');
            $table->string('name')->nullable();
            $table->string('currency', 10)->default('ILS');
            $table->string('timezone', 50)->default('Asia/Jerusalem');
            $table->string('access_token', 500)->nullable();
            $table->string('business_id', 100)->nullable();
            $table->decimal('spend_cap', 12, 2)->nullable()->comment('Account spending limit');
            $table->decimal('amount_spent', 12, 2)->default(0);
            $table->string('account_status', 30)->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ad_accounts');
    }
};
