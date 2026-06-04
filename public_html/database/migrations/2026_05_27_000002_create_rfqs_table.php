<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rfqs')) {
            return;
        }

        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1)->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('rfq_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('required_by_date')->nullable();
            $table->integer('estimated_quantity')->nullable();
            $table->text('delivery_address')->nullable();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'quoted', 'accepted', 'rejected', 'converted_to_order', 'expired'])->default('draft');
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->decimal('quoted_total', 10, 2)->nullable();
            $table->text('quote_notes')->nullable();
            $table->unsignedBigInteger('quoted_by')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->date('quote_valid_until')->nullable();
            $table->unsignedBigInteger('converted_order_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->json('attachments')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id')->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->integer('quantity');
            $table->json('specifications')->nullable();
            $table->decimal('quoted_unit_price', 10, 2)->nullable();
            $table->decimal('quoted_total_price', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('rfq_id')->references('id')->on('rfqs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfqs');
    }
};
