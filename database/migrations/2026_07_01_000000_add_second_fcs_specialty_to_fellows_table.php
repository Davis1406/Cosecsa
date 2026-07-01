<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->string('second_fcs_specialty', 100)->nullable()->after('fcs_certificate_number');
            $table->smallInteger('second_fcs_year')->nullable()->after('second_fcs_specialty');
        });
    }

    public function down(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->dropColumn(['second_fcs_specialty', 'second_fcs_year']);
        });
    }
};
