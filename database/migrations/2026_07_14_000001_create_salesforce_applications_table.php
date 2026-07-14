<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salesforce_applications', function (Blueprint $table) {
            $table->id();
            $table->string('sf_id')->unique();
            $table->string('name')->nullable();
            $table->string('applicant_name')->nullable();
            $table->string('applicant_email')->nullable();
            $table->string('applicant_phone')->nullable();
            $table->string('application_level')->nullable();
            $table->string('application_stage')->nullable();
            $table->string('programme_name')->nullable();
            $table->string('country')->nullable();
            $table->string('exam_year')->nullable();
            $table->date('date_of_application')->nullable();
            $table->string('entry_number')->nullable();
            $table->boolean('application_received')->default(false);
            $table->boolean('application_approved')->default(false);
            $table->timestamp('sf_created_at')->nullable();
            $table->timestamp('sf_modified_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index('application_stage');
            $table->index('exam_year');
        });

        Schema::create('salesforce_sync_log', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->unsignedInteger('records_synced')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesforce_sync_log');
        Schema::dropIfExists('salesforce_applications');
    }
};
