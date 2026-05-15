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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('subject');
            $table->longText('body');
            $table->timestamps();
        });

        // Default examiner bulk template
        DB::table('email_templates')->insert([
            'key'        => 'examiner_bulk',
            'subject'    => 'COSECSA ' . date('Y') . ' Examination — Examiner Invitation',
            'body'       => '<p>Dear [Name],</p><p>We are pleased to invite you to participate in the COSECSA ' . date('Y') . ' examination as an examiner.</p><p>Please confirm your availability at your earliest convenience.</p><p>We look forward to your continued support.</p>',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
