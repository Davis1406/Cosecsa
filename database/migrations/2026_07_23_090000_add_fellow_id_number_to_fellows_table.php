<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The unique College Fellow ID — distinct from candidate_number (a
    // programme/exam entry number) and from the auto-increment `id` PK.
    public function up(): void
    {
        if (Schema::hasColumn('fellows', 'fellow_id_number')) {
            return;
        }

        Schema::table('fellows', function (Blueprint $table) {
            $table->string('fellow_id_number', 50)->nullable()->unique()->after('programme_id');
        });
    }

    public function down(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->dropColumn('fellow_id_number');
        });
    }
};
