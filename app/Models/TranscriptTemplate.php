<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptTemplate extends Model
{
    protected $fillable = [
        'name', 'document_title', 'logo_path', 'watermark_path', 'intro_text', 'closing_salutation',
        'signatory_name', 'signatory_title', 'signature_path', 'stamp_path',
        'institution_name', 'address_text', 'footer_text', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];
}
