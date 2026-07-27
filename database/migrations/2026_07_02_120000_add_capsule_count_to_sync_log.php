<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capsule_sync_log', function (Blueprint $table) {
            $table->unsignedInteger('capsule_total')->nullable()->after('failed');
        });
    }

    public function down(): void
    {
        Schema::table('capsule_sync_log', function (Blueprint $table) {
            $table->dropColumn('capsule_total');
        });
    }
};
