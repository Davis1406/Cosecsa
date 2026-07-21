<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-editable letter wording — lets the wording/signatory change
        // without a code deploy, per "different templates which can be
        // edited by the admins" under Settings.
        Schema::create('transcript_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('document_title')->default('TRANSCRIPT OF TRAINING');
            $table->text('intro_text')->nullable();
            $table->string('closing_salutation')->default('Yours Sincerely,');
            $table->string('signatory_name');
            $table->string('signatory_title');
            $table->string('institution_name')->default('College of Surgeons of East, Central and Southern Africa');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // The editable candidate-detail header for one person's transcript.
        // Pre-filled from their fellow/trainee record when generated, then
        // freely editable by admin before issuing the PDF.
        Schema::create('transcript_records', function (Blueprint $table) {
            $table->id();
            // users.id is a legacy "int unsigned" column, not Laravel's
            // default bigint — foreignId() would mismatch types.
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('transcript_templates')->nullOnDelete();
            $table->string('full_name');
            $table->string('gender')->nullable();
            $table->string('programme_entry_number')->nullable();
            $table->string('medium_of_instruction')->default('English');
            $table->string('programme')->nullable();
            $table->string('entry_period')->nullable();
            $table->string('completion_period')->nullable();
            $table->string('final_score')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        // Itemized course rows, grouped by qualification (e.g. "MCS
        // (2019-2020)") — freely add/edit/remove/reorder from the admin form.
        Schema::create('transcript_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcript_record_id')->constrained('transcript_records')->cascadeOnDelete();
            $table->string('section')->nullable();    // e.g. "MCS (Membership of college of surgeons)"
            $table->string('subsection')->nullable();  // e.g. "MCS (2019 - 2020)"
            $table->string('course_name');
            $table->string('academic_year')->nullable();
            $table->string('result')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transcript_courses');
        Schema::dropIfExists('transcript_records');
        Schema::dropIfExists('transcript_templates');
    }
};
