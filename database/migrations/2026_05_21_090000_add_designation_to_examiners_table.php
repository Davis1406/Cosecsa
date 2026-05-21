<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examiners', function (Blueprint $table) {
            if (!Schema::hasColumn('examiners', 'examiner_designation')) {
                $table->string('examiner_designation', 60)->nullable()->after('role_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('examiners', function (Blueprint $table) {
            $table->dropColumn('examiner_designation');
        });
    }
};
