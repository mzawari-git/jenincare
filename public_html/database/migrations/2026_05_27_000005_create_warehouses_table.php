<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouses')) {
            return;
        }

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index(); // WH-RAM, WH-GAZ, etc.
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('country')->default('PS');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();
            $table->enum('type', ['main', 'branch', 'pickup', 'virtual'])->default('branch');
            $table->boolean('is_active')->default(true);
            $table->integer('capacity')->nullable(); // Storage capacity
            $table->json('operating_hours')->nullable(); // Opening hours
            $table->json('settings')->nullable(); // Warehouse-specific settings
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'type']);
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
