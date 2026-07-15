<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salesforce_applications', function (Blueprint $table) {
            $table->string('pen')->nullable()->after('entry_number');
            $table->string('hospital_name')->nullable()->after('country');
            $table->string('applicant_gender')->nullable()->after('applicant_phone');
            $table->unsignedBigInteger('trainee_id')->nullable()->after('application_approved');
        });
    }

    public function down(): void
    {
        Schema::table('salesforce_applications', function (Blueprint $table) {
            $table->dropColumn(['pen', 'hospital_name', 'applicant_gender', 'trainee_id']);
        });
    }
};
