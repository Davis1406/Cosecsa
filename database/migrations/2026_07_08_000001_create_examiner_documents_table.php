<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examiner_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exm_id');
            $table->string('title', 255);
            $table->string('file_path', 500);
            $table->string('original_name', 255);
            $table->string('file_type', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('exm_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examiner_documents');
    }
};
