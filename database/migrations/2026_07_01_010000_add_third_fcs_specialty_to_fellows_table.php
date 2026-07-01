<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->string('third_fcs_specialty', 100)->nullable()->after('second_fcs_year');
            $table->smallInteger('third_fcs_year')->nullable()->after('third_fcs_specialty');
        });
    }

    public function down(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->dropColumn(['third_fcs_specialty', 'third_fcs_year']);
        });
    }
};
