<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capsule_sync_log', function (Blueprint $table) {
            $table->string('status', 20)->default('idle')->after('id'); // idle|running|completed|failed
            $table->unsignedInteger('progress')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('capsule_sync_log', function (Blueprint $table) {
            $table->dropColumn(['status', 'progress']);
        });
    }
};
