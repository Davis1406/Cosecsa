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
        Schema::create('fellow_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fellow_id');
            $table->string('year', 10);
            $table->enum('status', ['Paid', 'Unpaid', 'Partial', 'Waived'])->default('Unpaid');
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->date('date_paid')->nullable();
            $table->string('mode_of_payment', 50)->nullable();
            $table->timestamps();

            $table->foreign('fellow_id')->references('id')->on('fellows')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fellow_subscriptions');
    }
};
