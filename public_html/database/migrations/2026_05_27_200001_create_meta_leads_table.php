<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_page_id')->nullable()->constrained('meta_pages')->onDelete('set null');
            $table->string('psid')->nullable()->index();
            $table->string('sender_name')->nullable();
            $table->string('sender_picture_url')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('country')->nullable()->default('PS');
            $table->string('age_range')->nullable()->index(); // 18-24, 25-34, 35-44, 45-54, 55-64, 65+
            $table->string('gender')->nullable()->index(); // male, female, unknown
            $table->string('locale')->nullable();
            $table->string('timezone')->nullable();
            $table->string('source')->default('facebook'); // facebook, instagram, website, pixel, ad
            $table->string('source_campaign')->nullable();
            $table->string('engagement_type')->nullable()->index(); // comment, message, ad_click, pixel_view, purchase
            $table->integer('lead_score')->default(0);
            $table->string('stage')->default('new')->index(); // hot, warm, engaged, new, cold, customer
            $table->string('intent')->nullable()->index(); // purchase, trust, awareness, readiness, complaint
            $table->decimal('purchase_probability', 5, 2)->nullable();
            $table->integer('total_interactions')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->json('tags')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_leads');
    }
};
