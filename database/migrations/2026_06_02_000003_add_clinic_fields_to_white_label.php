<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('white_label_settings', function (Blueprint $table) {
            $table->string('clinic_name_ar')->nullable()->after('app_name_en');
            $table->string('clinic_name_en')->nullable()->after('clinic_name_ar');
            $table->text('clinic_address_ar')->nullable()->after('clinic_name_en');
            $table->text('clinic_address_en')->nullable()->after('clinic_address_ar');
            $table->string('clinic_phone')->nullable()->after('clinic_address_en');
            $table->string('clinic_email')->nullable()->after('clinic_phone');
            $table->string('report_header_ar')->nullable()->after('clinic_email');
            $table->string('report_header_en')->nullable()->after('report_header_ar');
            $table->text('report_footer_ar')->nullable()->after('report_header_en');
            $table->text('report_footer_en')->nullable()->after('report_footer_ar');
        });
    }

    public function down(): void
    {
        Schema::table('white_label_settings', function (Blueprint $table) {
            $table->dropColumn([
                'clinic_name_ar', 'clinic_name_en',
                'clinic_address_ar', 'clinic_address_en',
                'clinic_phone', 'clinic_email',
                'report_header_ar', 'report_header_en',
                'report_footer_ar', 'report_footer_en',
            ]);
        });
    }
};
