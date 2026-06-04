<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1)->index();
            $table->unsignedBigInteger('order_id')->index()->unique();
            $table->string('delivery_number')->unique()->index();
            $table->enum('status', [
                'pending', 'assigned', 'picked_up', 'in_transit',
                'out_for_delivery', 'delivered', 'attempted', 'failed',
                'returned', 'cancelled'
            ])->default('pending')->index();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('driver_vehicle')->nullable();
            $table->string('courier_service')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->text('delivery_address');
            $table->string('delivery_city')->nullable();
            $table->string('delivery_region')->nullable();
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->string('delivery_zone')->nullable();
            $table->integer('estimated_delivery_days')->default(3);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('delivery_attempted_at')->nullable();
            $table->integer('delivery_attempts')->default(0);
            $table->string('failure_reason')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_signature')->nullable();
            $table->string('recipient_relation')->nullable();
            $table->decimal('cod_amount', 10, 2)->default(0);
            $table->enum('cod_status', ['pending', 'collected', 'settled', 'failed'])->nullable();
            $table->timestamp('cod_collected_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('status_history')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['driver_name', 'status']);
            $table->index(['delivery_city', 'status']);

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
