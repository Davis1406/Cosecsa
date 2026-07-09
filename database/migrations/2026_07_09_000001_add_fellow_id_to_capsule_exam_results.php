<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFellowIdToCapsuleExamResults extends Migration
{
    public function up()
    {
        Schema::table('capsule_exam_results', function (Blueprint $table) {
            $table->unsignedInteger('fellow_id')->nullable()->after('candidate_id');
            $table->index('fellow_id');
        });
    }

    public function down()
    {
        Schema::table('capsule_exam_results', function (Blueprint $table) {
            $table->dropIndex(['fellow_id']);
            $table->dropColumn('fellow_id');
        });
    }
}
