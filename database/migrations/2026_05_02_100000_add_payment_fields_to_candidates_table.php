<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->integer('invoice_amount')->nullable()->after('amount_paid');
            $table->enum('fee_paid', ['Yes', 'No'])->default('No')->after('invoice_amount');
            $table->date('payment_date')->nullable()->after('fee_paid');
            $table->string('mode_of_payment', 100)->nullable()->after('payment_date');
            $table->text('remarks')->nullable()->after('mode_of_payment');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['invoice_amount', 'fee_paid', 'payment_date', 'mode_of_payment', 'remarks']);
        });
    }
};
