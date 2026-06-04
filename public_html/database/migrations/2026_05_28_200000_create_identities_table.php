<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('fingerprint_hash', 64)->nullable()->index();
            $table->json('fingerprint_data')->nullable();
            $table->string('email_hash', 40)->nullable()->index();
            $table->string('phone_hash', 40)->nullable()->index();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['uuid', 'user_id']);
        });

        Schema::create('identity_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('event_type', 50)->index();
            $table->string('url')->nullable();
            $table->string('referer')->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('utm_term', 100)->nullable();
            $table->string('utm_content', 100)->nullable();
            $table->string('fbclid', 100)->nullable();
            $table->string('gclid', 100)->nullable();
            $table->string('ttclid', 100)->nullable();
            $table->string('twclid', 100)->nullable();
            $table->timestamps();

            $table->index(['uuid', 'event_type']);
            $table->index(['uuid', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_events');
        Schema::dropIfExists('identities');
    }
};
