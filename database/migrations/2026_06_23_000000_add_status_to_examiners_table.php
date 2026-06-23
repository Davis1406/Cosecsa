<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToExaminersTable extends Migration
{
    public function up()
    {
        Schema::table('examiners', function (Blueprint $table) {
            if (!Schema::hasColumn('examiners', 'status')) {
                $table->string('status', 20)->default('Active')->after('examiner_designation');
            }
        });
    }

    public function down()
    {
        Schema::table('examiners', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
