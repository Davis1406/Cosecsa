<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Some fellows genuinely have no confirmed category (their original
        // import defaulted them into "Fellow by Examination" with no real
        // evidence either way) — allow the field to be left blank instead
        // of forcing a guess.
        DB::statement('ALTER TABLE fellows MODIFY category_id INT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE fellows SET category_id = 5 WHERE category_id IS NULL");
        DB::statement('ALTER TABLE fellows MODIFY category_id INT NOT NULL');
    }
};
