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
        if (Schema::hasTable('fellow_exam_results')) {
            return;
        }
        Schema::create('fellow_exam_results', function (Blueprint $table) {
            $table->id();
            $table->integer('fellow_id');
            $table->smallInteger('year');
            $table->tinyInteger('part');           // 1 or 2
            $table->string('exam_type', 20)->nullable();  // GS, ORTH, PAED, URO, etc.
            $table->decimal('score', 8, 4)->nullable();
            $table->string('result', 10)->nullable();      // PASS / FAIL
            $table->string('raw_result', 255)->nullable(); // full original string
            $table->timestamps();

            $table->foreign('fellow_id')->references('id')->on('fellows')->onDelete('cascade');
            $table->unique(['fellow_id', 'year', 'part'], 'uq_fellow_year_part');
            $table->index('fellow_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fellow_exam_results');
    }
};
