<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examiners', function (Blueprint $table) {
            if (!Schema::hasColumn('examiners', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('passport_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('examiners', function (Blueprint $table) {
            $table->dropColumn('internal_notes');
        });
    }
};
