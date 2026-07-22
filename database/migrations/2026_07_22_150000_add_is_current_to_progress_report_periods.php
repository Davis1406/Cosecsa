<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Only the single most recent period is "current" — a pending (never
    // submitted) section on any older period is locked just like a
    // submitted one, since we've moved on to reporting a later month.
    public function up(): void
    {
        Schema::table('progress_report_periods', function (Blueprint $table) {
            $table->boolean('is_current')->default(false)->after('status');
        });

        $latestId = DB::table('progress_report_periods')->orderByDesc('period_month')->value('id');
        if ($latestId) {
            DB::table('progress_report_periods')->where('id', $latestId)->update(['is_current' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('progress_report_periods', function (Blueprint $table) {
            $table->dropColumn('is_current');
        });
    }
};
