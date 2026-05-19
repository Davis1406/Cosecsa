<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('examiner_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('mobile')->nullable();
            $table->string('specialty')->nullable();
            $table->string('subspecialty')->nullable();
            $table->tinyInteger('shift')->nullable();
            $table->string('curriculum_vitae')->nullable();
            $table->string('passport_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
