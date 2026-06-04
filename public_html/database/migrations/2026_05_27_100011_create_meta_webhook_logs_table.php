<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meta_page_id')->nullable();
            $table->string('object', 50)->comment('page, instagram, etc.');
            $table->string('entry_type', 50)->comment('feed, messages, messaging_postbacks');
            $table->json('payload')->comment('Full webhook payload');
            $table->string('psid', 100)->nullable()->index()->comment('Page Scoped User ID');
            $table->string('sender_name')->nullable();
            $table->text('message_text')->nullable();
            $table->string('comment_id', 100)->nullable();
            $table->string('post_id', 100)->nullable();
            $table->boolean('is_processed')->default(false);
            $table->boolean('is_replied')->default(false);
            $table->json('reply_data')->nullable();
            $table->string('status', 50)->default('new');
            $table->timestamps();

            $table->foreign('meta_page_id')->references('id')->on('meta_pages')->onDelete('set null');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_webhook_logs');
    }
};
