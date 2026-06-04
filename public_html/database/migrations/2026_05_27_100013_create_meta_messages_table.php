<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('meta_page_id')->nullable();
            $table->string('message_id', 100)->nullable()->index()->comment('Facebook message ID');
            $table->string('direction', 20)->default('incoming')->comment('incoming, outgoing');
            $table->text('message_text')->nullable();
            $table->json('attachments')->nullable();
            $table->json('quick_reply')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_echo')->default(false);
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('meta_conversations')->onDelete('cascade');
            $table->foreign('meta_page_id')->references('id')->on('meta_pages')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_messages');
    }
};
