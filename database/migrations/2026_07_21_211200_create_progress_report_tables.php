<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_report_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('due_day')->default(24);
            $table->unsignedTinyInteger('reminder_days_before')->default(3);
            $table->boolean('reminder_enabled')->default(true);
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('progress_report_periods', function (Blueprint $table) {
            $table->id();
            $table->date('period_month')->unique(); // first of month
            $table->date('due_date');
            $table->enum('status', ['open', 'consolidated'])->default('open');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('consolidated_at')->nullable();
            $table->unsignedInteger('consolidated_by')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('consolidated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('progress_report_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('period_id');
            $table->unsignedInteger('user_id');
            $table->string('section_label');
            $table->enum('status', ['pending', 'submitted'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('progress_report_periods')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['period_id', 'user_id']);
        });

        Schema::create('progress_report_task_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id'); // default section owner
            $table->string('activity_description');
            $table->text('default_planned_activities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('progress_report_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('period_id');
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedInteger('row_no')->default(1);
            $table->string('activity_description')->nullable();
            $table->text('planned_activities')->nullable();
            $table->text('current_status')->nullable();
            $table->text('next_steps')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('progress_report_periods')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('progress_report_participants')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('progress_report_task_templates')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('progress_report_task_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedInteger('editor_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('task_id')->references('id')->on('progress_report_tasks')->onDelete('cascade');
            $table->foreign('editor_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_report_task_revisions');
        Schema::dropIfExists('progress_report_tasks');
        Schema::dropIfExists('progress_report_task_templates');
        Schema::dropIfExists('progress_report_participants');
        Schema::dropIfExists('progress_report_periods');
        Schema::dropIfExists('progress_report_settings');
    }
};
