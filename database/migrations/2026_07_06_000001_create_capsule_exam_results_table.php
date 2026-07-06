<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCapsuleExamResultsTable extends Migration
{
    public function up()
    {
        Schema::create('capsule_exam_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('capsule_id')->nullable()->comment('Capsule CRM party ID');
            $table->string('contact_name')->nullable();
            $table->unsignedInteger('trainee_id')->nullable();
            $table->unsignedInteger('candidate_id')->nullable();
            $table->unsignedInteger('programme_id')->nullable();
            $table->string('specialty', 100)->nullable();
            $table->smallInteger('exam_year');
            $table->string('exam_type', 50)->nullable()->comment('Written, Clinical, FCS, MCS, etc.');
            $table->decimal('score', 8, 4)->nullable();
            $table->string('result', 10)->comment('Pass, Fail, Absent');
            $table->text('raw_note')->nullable();
            $table->date('note_date')->nullable();
            $table->timestamps();

            $table->unique(['capsule_id', 'exam_year', 'specialty', 'exam_type'], 'uq_capsule_note');
            $table->index('trainee_id');
            $table->index('candidate_id');
            $table->index('exam_year');
            $table->index('result');
        });
    }

    public function down()
    {
        Schema::dropIfExists('capsule_exam_results');
    }
}
