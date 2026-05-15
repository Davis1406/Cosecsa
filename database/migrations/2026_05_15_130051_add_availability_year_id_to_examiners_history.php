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
        Schema::table('examiners_history', function (Blueprint $table) {
            // Tracks which exam year the stored exam_availability belongs to.
            // When this differs from the current year the edit/view pages treat
            // availability as not-yet-confirmed and show all boxes unchecked.
            $table->unsignedInteger('availability_year_id')->nullable()->after('exam_availability');
        });
    }

    public function down(): void
    {
        Schema::table('examiners_history', function (Blueprint $table) {
            $table->dropColumn('availability_year_id');
        });
    }
};
