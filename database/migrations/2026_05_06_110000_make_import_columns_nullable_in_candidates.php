<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeImportColumnsNullableInCandidates extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->integer('hospital_id')->nullable()->change();
            $table->integer('country_id')->nullable()->change();
            $table->string('personal_email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->integer('hospital_id')->nullable(false)->change();
            $table->integer('country_id')->nullable(false)->change();
            $table->string('personal_email')->nullable(false)->change();
        });
    }
}
