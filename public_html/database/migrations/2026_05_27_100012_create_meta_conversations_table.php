<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meta_page_id')->nullable();
            $table->string('psid', 100)->index()->comment('Page Scoped User ID');
            $table->string('sender_name')->nullable();
            $table->string('sender_picture_url')->nullable();
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->integer('message_count')->default(0);
            $table->string('intent', 50)->nullable()->comment('purchase, trust, awareness, readiness');
            $table->string('stage', 50)->default('new')->comment('new, engaged, hot_lead, customer, cold');
            $table->integer('lead_score')->default(0);
            $table->string('notification_channel')->nullable()->comment('messenger, whatsapp, email, sms');
            $table->json('tags')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();

            $table->foreign('meta_page_id')->references('id')->on('meta_pages')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_conversations');
    }
};
