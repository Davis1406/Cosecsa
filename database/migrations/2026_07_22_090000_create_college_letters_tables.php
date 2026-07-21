<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('college_letterhead_settings', function (Blueprint $table) {
            $table->id();
            $table->string('institution_name')->default('College of Surgeons of East, Central and Southern Africa (COSECSA)');
            $table->text('address_text')->nullable();
            $table->text('footer_text')->nullable(); // "Label: Value||Label: Value" per line, matches transcript template convention
            $table->string('logo_path')->nullable();
            $table->string('watermark_path')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->longText('pdf_body');   // the letter content rendered on letterhead, may contain {{merge_fields}}
            $table->longText('email_body'); // the accompanying email message, may contain {{merge_fields}}
            $table->string('recipient_source'); // trainees|candidates|fellows|examiners|country_reps|trainers
            $table->string('legacy_status_field')->nullable(); // e.g. admission_letter_status on trainees
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('letter_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_template_id')->constrained('letter_templates')->cascadeOnDelete();
            $table->unsignedInteger('sent_by');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('sent_by')->references('id')->on('users');
        });

        Schema::create('letter_dispatch_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('letter_dispatches')->cascadeOnDelete();
            $table->foreignId('letter_template_id')->constrained('letter_templates')->cascadeOnDelete();
            $table->string('recipient_source');
            $table->unsignedBigInteger('recipient_id');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_source', 'recipient_id'], 'ldr_source_id_idx');
            $table->index(['letter_template_id', 'recipient_source', 'recipient_id'], 'ldr_template_source_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_dispatch_recipients');
        Schema::dropIfExists('letter_dispatches');
        Schema::dropIfExists('letter_templates');
        Schema::dropIfExists('college_letterhead_settings');
    }
};
