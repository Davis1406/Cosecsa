<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterDispatchRecipient extends Model
{
    protected $fillable = [
        'dispatch_id', 'letter_template_id', 'recipient_source', 'recipient_id',
        'recipient_name', 'recipient_email', 'pdf_path', 'status', 'error_message', 'sent_at',
    ];
    protected $casts = ['sent_at' => 'datetime'];

    public function dispatch()
    {
        return $this->belongsTo(LetterDispatch::class, 'dispatch_id');
    }

    public function template()
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }
}
