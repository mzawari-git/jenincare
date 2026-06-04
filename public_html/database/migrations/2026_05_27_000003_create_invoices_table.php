<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
