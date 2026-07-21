<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->string('sfs_username')->nullable()->after('invitation_letter_status');
            $table->string('sfs_password')->nullable()->after('sfs_username');
        });
    }

    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->dropColumn(['sfs_username', 'sfs_password']);
        });
    }
};
