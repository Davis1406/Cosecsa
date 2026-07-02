<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('examiners_history', function (Blueprint $table) {
            $table->enum('source', ['self', 'admin'])->nullable()->after('examination_years');
        });
    }

    public function down(): void
    {
        Schema::table('examiners_history', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
