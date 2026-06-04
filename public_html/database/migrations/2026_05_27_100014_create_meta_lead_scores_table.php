<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_lead_scores', function (Blueprint $table) {
            $table->id();
            $table->string('psid', 100)->index();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->string('event_type', 50)->comment('view_content, add_to_cart, open_messenger, ignore_message, visit_checkout, purchase');
            $table->integer('score_delta')->comment('Points added or subtracted');
            $table->integer('total_score')->default(0);
            $table->string('segment', 30)->nullable()->comment('hot, warm, cold');
            $table->float('purchase_probability')->nullable();
            $table->json('event_data')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('meta_conversations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_lead_scores');
    }
};
