<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptTemplate extends Model
{
    protected $fillable = [
        'name', 'document_title', 'intro_text', 'closing_salutation',
        'signatory_name', 'signatory_title', 'institution_name', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];
}
