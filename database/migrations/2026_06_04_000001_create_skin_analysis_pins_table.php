<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skin_analysis_pins', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('scan_id')->constrained('skin_scans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pin_type')->default('featured');
            $table->text('admin_note')->nullable();
            $table->text('admin_note_ar')->nullable();
            $table->timestamp('pinned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['scan_id', 'pin_type']);
            $table->index('pinned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skin_analysis_pins');
    }
};
