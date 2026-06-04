<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_pixel_events', function (Blueprint $table) {
            $table->id();
            $table->string('pixel_id')->nullable()->index();
            $table->string('event_name')->index();
            $table->string('event_id')->nullable()->index();
            $table->string('event_source_url')->nullable();
            $table->string('fbp')->nullable(); // Facebook browser pixel
            $table->string('fbc')->nullable(); // Facebook click ID
            $table->string('external_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action_source')->nullable(); // website, email, chat, app
            $table->json('user_data')->nullable();
            $table->json('custom_data')->nullable();
            $table->decimal('value', 12, 2)->nullable();
            $table->string('currency')->nullable()->default('ILS');
            $table->string('city')->nullable()->index();
            $table->string('country')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->string('campaign_id')->nullable()->index();
            $table->string('ad_set_id')->nullable();
            $table->string('ad_id')->nullable();
            $table->json('match_keys')->nullable();
            $table->timestamps();
            $table->index(['event_name', 'created_at']);
        });

        Schema::create('meta_bulk_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('message_text');
            $table->json('quick_replies')->nullable();
            $table->string('status')->default('draft'); // draft, sending, completed, failed
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('read_count')->default(0);
            $table->integer('reply_count')->default(0);
            $table->json('recipient_filters')->nullable(); // city, age, gender, stage, etc
            $table->json('recipient_ids')->nullable();
            $table->json('delivery_log')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_bulk_campaigns');
        Schema::dropIfExists('meta_pixel_events');
    }
};
