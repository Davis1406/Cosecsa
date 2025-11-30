// 1. Cardiothoracic Results Table
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cardiothoracic_results', function (Blueprint $table) {
            $table->id();
            $table->integer('candidate_id');
            $table->integer('examiner_id');
            $table->integer('station_id');
            $table->integer('group_id');
            $table->string('exam_format'); // 'clinical' or 'viva'
            $table->json('question_mark');
            $table->integer('total');
            $table->text('remarks')->nullable();
            $table->integer('exam_year');
            $table->timestamps();

            $table->foreign('candidate_id')->references('id')->on('candidates')->onDelete('cascade');
            $table->foreign('examiner_id')->references('id')->on('examiners')->onDelete('cascade');
            $table->foreign('exam_year')->references('id')->on('years')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cardiothoracic_results');
    }
};
