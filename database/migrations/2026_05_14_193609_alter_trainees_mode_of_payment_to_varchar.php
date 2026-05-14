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
        // Must widen to VARCHAR first, THEN normalise values (ENUM won't accept new values).
        DB::statement("ALTER TABLE trainees MODIFY COLUMN mode_of_payment VARCHAR(100) NULL DEFAULT NULL");
        DB::statement("UPDATE trainees SET mode_of_payment = 'Bank Transfer'  WHERE mode_of_payment = 'Bank transfer'");
        DB::statement("UPDATE trainees SET mode_of_payment = 'Online Payment' WHERE mode_of_payment = 'Online Payment System'");
        DB::statement("UPDATE trainees SET mode_of_payment = NULL             WHERE mode_of_payment = ''");
    }

    public function down(): void
    {
        // Restore ENUM – map values back
        DB::statement("UPDATE trainees SET mode_of_payment = 'Bank transfer'          WHERE mode_of_payment = 'Bank Transfer'");
        DB::statement("UPDATE trainees SET mode_of_payment = 'Online Payment System'  WHERE mode_of_payment = 'Online Payment'");
        DB::statement("ALTER TABLE trainees MODIFY COLUMN mode_of_payment ENUM('Country Rep','Bank transfer','Online Payment System','') NULL DEFAULT NULL");
    }
};
