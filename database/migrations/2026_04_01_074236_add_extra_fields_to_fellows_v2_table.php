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
        $cols = [
            'academic_qualifications', 'mcs_certificate_number', 'fcs_certificate_number',
            'registration_date_mcs', 'registration_date_fcs', 'prog_entry_fee_verified',
            'specialty_qualification_date', 'cert_name',
        ];
        $existing = array_filter($cols, fn($c) => Schema::hasColumn('fellows', $c));
        $missing  = array_diff($cols, $existing);
        if (empty($missing)) {
            return; // All columns already present on this server
        }

        Schema::table('fellows', function (Blueprint $table) use ($missing) {
            if (in_array('academic_qualifications', $missing))
                $table->text('academic_qualifications')->nullable()->after('second_email');
            if (in_array('mcs_certificate_number', $missing))
                $table->string('mcs_certificate_number', 50)->nullable()->after('academic_qualifications');
            if (in_array('fcs_certificate_number', $missing))
                $table->string('fcs_certificate_number', 50)->nullable()->after('mcs_certificate_number');
            if (in_array('registration_date_mcs', $missing))
                $table->date('registration_date_mcs')->nullable()->after('fcs_certificate_number');
            if (in_array('registration_date_fcs', $missing))
                $table->date('registration_date_fcs')->nullable()->after('registration_date_mcs');
            if (in_array('prog_entry_fee_verified', $missing))
                $table->boolean('prog_entry_fee_verified')->nullable()->after('registration_date_fcs');
            if (in_array('specialty_qualification_date', $missing))
                $table->date('specialty_qualification_date')->nullable()->after('prog_entry_fee_verified');
            if (in_array('cert_name', $missing))
                $table->string('cert_name', 255)->nullable()->after('specialty_qualification_date')
                      ->comment('Name as it should appear on certificate');
        });
    }

    public function down(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->dropColumn([
                'academic_qualifications',
                'mcs_certificate_number',
                'fcs_certificate_number',
                'registration_date_mcs',
                'registration_date_fcs',
                'prog_entry_fee_verified',
                'specialty_qualification_date',
                'cert_name',
            ]);
        });
    }
};
