<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospital_programmes', function (Blueprint $table) {
            $table->timestamp('last_reminder_sent_at')->nullable()->after('status');
        });

        Schema::table('hospitals', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('hospital_type');
        });
    }

    public function down(): void
    {
        Schema::table('hospital_programmes', function (Blueprint $table) {
            $table->dropColumn('last_reminder_sent_at');
        });
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropColumn('contact_email');
        });
    }
};
