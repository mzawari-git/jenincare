<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1)->index();

            $table->string('company_code')->unique()->index();
            $table->string('company_name_ar');
            $table->string('company_name_en')->nullable();
            $table->enum('company_type', [
                'salon', 'clinic', 'spa', 'distributor', 'retailer', 'wholesaler'
            ])->index();

            $table->string('primary_contact_name');
            $table->string('primary_contact_phone');
            $table->string('primary_contact_email')->unique();
            $table->string('secondary_contact_phone')->nullable();
            $table->string('secondary_contact_email')->nullable();
            $table->string('whatsapp_number')->nullable();

            $table->text('address');
            $table->string('city');
            $table->string('region')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('PS');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->string('tax_id')->nullable();
            $table->string('commercial_registration')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_expiry_date')->nullable();

            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->integer('payment_terms_days')->default(30);
            $table->boolean('credit_approved')->default(false);
            $table->unsignedBigInteger('credit_approved_by')->nullable();
            $table->timestamp('credit_approved_at')->nullable();

            $table->decimal('default_discount_percentage', 5, 2)->default(0);
            $table->json('tier_discounts')->nullable();
            $table->boolean('has_free_shipping')->default(false);
            $table->integer('minimum_order_quantity')->default(10);
            $table->decimal('minimum_order_amount', 10, 2)->default(0);

            $table->enum('status', ['pending', 'active', 'suspended', 'inactive'])->default('pending')->index();
            $table->integer('trust_score')->default(0);
            $table->decimal('average_order_value', 10, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('lifetime_value', 12, 2)->default(0);
            $table->timestamp('last_order_date')->nullable();

            $table->integer('team_size')->nullable();
            $table->json('team_members')->nullable();

            $table->text('notes')->nullable();
            $table->json('documents')->nullable();
            $table->json('certifications')->nullable();

            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'company_type']);
            $table->index('credit_approved');
        });

        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1)->index();

            $table->string('rfq_number')->unique()->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('title');
            $table->text('description');
            $table->date('required_by_date')->nullable();
            $table->integer('estimated_quantity');
            $table->string('delivery_address')->nullable();

            $table->enum('status', [
                'draft', 'submitted', 'under_review', 'quoted',
                'accepted', 'rejected', 'converted_to_order', 'expired'
            ])->default('draft')->index();

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

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id')->index();
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->integer('quantity');
            $table->json('specifications')->nullable();

            $table->decimal('quoted_unit_price', 10, 2)->nullable();
            $table->decimal('quoted_total_price', 10, 2)->nullable();

            $table->timestamps();

            $table->foreign('rfq_id')->references('id')->on('rfqs')->onDelete('cascade');
        });

        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();

            $table->enum('type', ['purchase', 'payment', 'credit_note', 'debit_note', 'adjustment']);

            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);

            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'credit_card', 'other'])->nullable();

            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('transaction_date');

            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('order_id')->index();

            $table->date('invoice_date');
            $table->date('due_date');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);

            $table->enum('status', [
                'draft', 'sent', 'viewed', 'partially_paid',
                'paid', 'overdue', 'cancelled'
            ])->default('draft')->index();

            $table->text('notes')->nullable();
            $table->json('payment_history')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfqs');
        Schema::dropIfExists('companies');
    }
};
