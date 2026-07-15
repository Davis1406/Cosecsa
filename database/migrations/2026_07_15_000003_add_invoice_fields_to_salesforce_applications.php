<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salesforce_applications', function (Blueprint $table) {
            // Programme Entry Fee invoice, from the linked Invoice__c record
            // (Invoice_Type__c = 'Application') — used when populating trainees.
            $table->string('entry_invoice_number')->nullable()->after('pen');
            $table->decimal('entry_invoice_amount', 10, 2)->nullable()->after('entry_invoice_number');
            $table->decimal('entry_payment_amount', 10, 2)->nullable()->after('entry_invoice_amount');
            $table->date('entry_payment_date')->nullable()->after('entry_payment_amount');
            $table->string('entry_payment_method')->nullable()->after('entry_payment_date');
            $table->string('entry_invoice_status')->nullable()->after('entry_payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('salesforce_applications', function (Blueprint $table) {
            $table->dropColumn([
                'entry_invoice_number', 'entry_invoice_amount', 'entry_payment_amount',
                'entry_payment_date', 'entry_payment_method', 'entry_invoice_status',
            ]);
        });
    }
};
