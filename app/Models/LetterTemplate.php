<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    protected $fillable = [
        'name', 'subject', 'pdf_body', 'email_body', 'recipient_source',
        'legacy_status_field', 'is_active', 'created_by',
    ];
    protected $casts = ['is_active' => 'boolean'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dispatches()
    {
        return $this->hasMany(LetterDispatch::class);
    }
}
