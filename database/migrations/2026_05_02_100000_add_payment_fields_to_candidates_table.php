<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $cols    = ['invoice_amount', 'fee_paid', 'payment_date', 'mode_of_payment', 'remarks'];
        $missing = array_filter($cols, fn($c) => !Schema::hasColumn('candidates', $c));
        if (empty($missing)) {
            return;
        }
        Schema::table('candidates', function (Blueprint $table) use ($missing) {
            if (in_array('invoice_amount',  $missing)) $table->integer('invoice_amount')->nullable()->after('amount_paid');
            if (in_array('fee_paid',        $missing)) $table->enum('fee_paid', ['Yes', 'No'])->default('No')->after('invoice_amount');
            if (in_array('payment_date',    $missing)) $table->date('payment_date')->nullable()->after('fee_paid');
            if (in_array('mode_of_payment', $missing)) $table->string('mode_of_payment', 100)->nullable()->after('payment_date');
            if (in_array('remarks',         $missing)) $table->text('remarks')->nullable()->after('mode_of_payment');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['invoice_amount', 'fee_paid', 'payment_date', 'mode_of_payment', 'remarks']);
        });
    }
};
