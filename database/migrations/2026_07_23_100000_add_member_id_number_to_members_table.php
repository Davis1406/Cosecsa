<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The unique College Member ID — same idea as fellows.fellow_id_number,
    // scoped to the members table.
    public function up(): void
    {
        if (Schema::hasColumn('members', 'member_id_number')) {
            return;
        }

        Schema::table('members', function (Blueprint $table) {
            $table->string('member_id_number', 50)->nullable()->unique()->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('member_id_number');
        });
    }
};
