<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->string('candidate_number', 50)->nullable()->after('admission_year');
            $table->text('supervised_by')->nullable()->after('candidate_number');
            $table->string('registered_by', 100)->nullable()->after('supervised_by');
            $table->date('secretariat_registration_date')->nullable()->after('registered_by');
            $table->string('prog_entry_fee_year', 10)->nullable()->after('secretariat_registration_date');
            $table->string('prog_entry_mode_payment', 50)->nullable()->after('prog_entry_fee_year');
            $table->string('exam_fee_year', 10)->nullable()->after('prog_entry_mode_payment');
            $table->date('exam_fee_date_paid')->nullable()->after('exam_fee_year');
            $table->string('exam_fee_mode_payment', 50)->nullable()->after('exam_fee_date_paid');
            $table->string('exam_fee_amount_paid', 20)->nullable()->after('exam_fee_mode_payment');
            $table->tinyInteger('exam_fee_payment_verified')->default(0)->after('exam_fee_amount_paid');
            $table->string('sponsored_by', 255)->nullable()->after('exam_fee_payment_verified');
            $table->string('mcs_qualification_year', 10)->nullable()->after('sponsored_by');
            $table->string('country_mcs_training', 100)->nullable()->after('mcs_qualification_year');
            $table->string('exam_year_upcoming', 10)->nullable()->after('country_mcs_training');
            $table->string('exam_year_previous', 10)->nullable()->after('exam_year_upcoming');
            $table->string('cosecsa_region', 100)->nullable()->after('exam_year_previous');
            $table->string('second_email', 255)->nullable()->after('cosecsa_region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->dropColumn([
                'candidate_number','supervised_by','registered_by',
                'secretariat_registration_date','prog_entry_fee_year',
                'prog_entry_mode_payment','exam_fee_year','exam_fee_date_paid',
                'exam_fee_mode_payment','exam_fee_amount_paid','exam_fee_payment_verified',
                'sponsored_by','mcs_qualification_year','country_mcs_training',
                'exam_year_upcoming','exam_year_previous','cosecsa_region','second_email',
            ]);
        });
    }
};
