<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE candidates MODIFY COLUMN exam_year ENUM('2024','2025','2026','2027','')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE candidates MODIFY COLUMN exam_year ENUM('2024','2025','2026','')");
    }
};
