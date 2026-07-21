<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transcript_templates', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('document_title');
            $table->string('watermark_path')->nullable()->after('logo_path');
            $table->string('signature_path')->nullable()->after('signatory_name');
            $table->string('stamp_path')->nullable()->after('signature_path');
            $table->text('address_text')->nullable()->after('institution_name');
            $table->text('footer_text')->nullable()->after('address_text');
        });
    }

    public function down(): void
    {
        Schema::table('transcript_templates', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'watermark_path', 'signature_path', 'stamp_path', 'address_text', 'footer_text']);
        });
    }
};
