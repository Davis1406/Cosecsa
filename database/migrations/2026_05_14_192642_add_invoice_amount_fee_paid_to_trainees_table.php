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
        Schema::table('trainees', function (Blueprint $table) {
            // invoice_amount = what was invoiced (PE fee); amount_paid = what was actually received
            $table->decimal('invoice_amount', 10, 2)->nullable()->after('invoice_date');
            // fee_paid mirrors the candidates field so both tables share the same semantics
            $table->string('fee_paid', 10)->nullable()->after('invoice_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->dropColumn(['invoice_amount', 'fee_paid']);
        });
    }
};
