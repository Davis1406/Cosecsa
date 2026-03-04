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
        Schema::create('fellow_label_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fellow_id');
            $table->unsignedBigInteger('label_id');
            $table->timestamps();
            $table->foreign('fellow_id')->references('id')->on('fellows')->onDelete('cascade');
            $table->foreign('label_id')->references('id')->on('fellow_labels')->onDelete('cascade');
            $table->unique(['fellow_id', 'label_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fellow_label_assignments');
    }
};
