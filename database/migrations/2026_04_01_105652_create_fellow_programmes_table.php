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
        Schema::create('fellow_programmes', function (Blueprint $table) {
            $table->id();
            $table->integer('fellow_id');
            $table->integer('programme_id');
            $table->string('fellowship_year', 10)->nullable();
            $table->string('source', 50)->default('import');
            $table->timestamps();

            $table->unique(['fellow_id', 'programme_id']);
            $table->foreign('fellow_id')->references('id')->on('fellows')->onDelete('cascade');
            $table->foreign('programme_id')->references('id')->on('programmes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fellow_programmes');
    }
};
