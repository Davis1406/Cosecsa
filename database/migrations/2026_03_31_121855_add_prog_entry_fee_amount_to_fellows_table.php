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
            // Amount paid for the programme entry fee (from Capsule custom field)
            $table->decimal('prog_entry_fee_amount_paid', 10, 2)->nullable()->after('prog_entry_fee_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fellows', function (Blueprint $table) {
            $table->dropColumn('prog_entry_fee_amount_paid');
        });
    }
};
