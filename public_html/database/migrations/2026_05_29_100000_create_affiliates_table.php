<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->unique()->index();
            $table->string('name');
            $table->string('email')->unique()->index();
            $table->string('phone')->nullable();
            $table->string('referral_code', 50)->unique()->index();
            $table->string('discount_code', 50)->nullable()->unique()->index();
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending')->index();
            $table->string('tier_level', 20)->default('bronze');
            $table->string('commission_type', 20)->default('percentage');
            $table->decimal('commission_value', 8, 2)->default(10.00);
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->decimal('total_earned', 12, 2)->default(0.00);
            $table->decimal('total_paid', 12, 2)->default(0.00);
            $table->integer('fraud_score')->default(0);
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
