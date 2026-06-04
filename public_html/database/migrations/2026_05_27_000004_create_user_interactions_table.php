<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_interactions')) {
            return;
        }

        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->enum('action', ['view', 'add_to_cart', 'remove_from_cart', 'wishlist_add', 'wishlist_remove', 'purchase', 'review', 'search', 'recommendation_click']);
            $table->json('metadata')->nullable(); // Additional data like time spent, position, etc.
            $table->decimal('rating', 3, 2)->nullable(); // User rating if applicable
            $table->timestamps();

            $table->index(['user_id', 'action']);
            $table->index(['product_id', 'action']);
            $table->index(['user_id', 'product_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_interactions');
    }
};
