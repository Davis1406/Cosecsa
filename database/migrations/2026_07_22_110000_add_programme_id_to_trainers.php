<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            // Nullable: existing trainers stay "hospital-wide" PDs (shown for
            // every programme at that hospital) unless scoped to one specific
            // programme via the Hospital Accreditation dashboard's quick PD
            // add/edit action.
            // programmes.id is a plain (signed) int PK, not the default
            // bigint — match it exactly or the FK add fails with an
            // "incompatible" type error.
            $table->integer('programme_id')->nullable()->after('hospital_id');
            $table->foreign('programme_id')->references('id')->on('programmes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            $table->dropForeign(['programme_id']);
            $table->dropColumn('programme_id');
        });
    }
};
