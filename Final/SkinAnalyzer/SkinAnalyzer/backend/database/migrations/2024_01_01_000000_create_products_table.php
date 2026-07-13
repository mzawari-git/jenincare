<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable()->index();
            $table->string('skin_type')->nullable();
            $table->json('concerns')->nullable();
            $table->json('ingredients')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 10)->default('ILS');
            $table->string('image_path')->nullable();
            $table->string('image_url')->nullable();
            $table->string('affiliate_url')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'is_active']);
            $table->index(['is_active', 'stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
