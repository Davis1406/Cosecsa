<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('country_reps', function (Blueprint $table) {
            // Distinguishes "Country Representative" from "Overseas
            // Representative" / "WiSA chair" — the list has more than one
            // kind of representative per country.
            $table->string('position')->default('Country Representative')->after('country_id');
        });
    }

    public function down(): void
    {
        Schema::table('country_reps', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
