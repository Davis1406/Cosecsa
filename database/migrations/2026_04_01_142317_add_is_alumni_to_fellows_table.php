<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->tinyInteger('is_alumni')->default(0)->after('is_promoted')
                  ->comment('1 = confirmed in the alumni Excel sheet');
        });
    }

    public function down(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->dropColumn('is_alumni');
        });
    }
};
