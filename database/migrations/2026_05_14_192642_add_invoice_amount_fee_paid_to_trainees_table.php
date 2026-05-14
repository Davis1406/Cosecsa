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
        $cols    = ['invoice_amount', 'fee_paid'];
        $missing = array_filter($cols, fn($c) => !Schema::hasColumn('trainees', $c));
        if (empty($missing)) {
            return;
        }
        Schema::table('trainees', function (Blueprint $table) use ($missing) {
            // invoice_amount = what was invoiced (PE fee); amount_paid = what was actually received
            if (in_array('invoice_amount', $missing))
                $table->decimal('invoice_amount', 10, 2)->nullable()->after('invoice_date');
            // fee_paid mirrors the candidates field so both tables share the same semantics
            if (in_array('fee_paid', $missing))
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
