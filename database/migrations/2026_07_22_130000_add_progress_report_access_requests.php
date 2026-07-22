<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progress_report_participants', function (Blueprint $table) {
            $table->boolean('edit_unlocked')->default(false)->after('submitted_at');
        });

        Schema::create('progress_report_access_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id');
            $table->unsignedInteger('requested_by');
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');
            $table->text('reason')->nullable();
            $table->unsignedInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->foreign('participant_id')->references('id')->on('progress_report_participants')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('decided_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_report_access_requests');
        Schema::table('progress_report_participants', function (Blueprint $table) {
            $table->dropColumn('edit_unlocked');
        });
    }
};
