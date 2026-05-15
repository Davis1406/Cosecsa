<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('examiner_participations')) {
            Schema::create('examiner_participations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('exm_id');
                $table->unsignedInteger('year_id');
                $table->string('specialty')->nullable();
                $table->string('role', 100)->nullable();
                $table->string('sub_specialty')->nullable();
                $table->string('fellowship_no', 100)->nullable();
                $table->enum('source', ['upload', 'manual'])->default('upload');
                $table->timestamps();
                $table->unique(['exm_id', 'year_id', 'specialty'], 'exm_year_specialty_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('examiner_participations');
    }
};
