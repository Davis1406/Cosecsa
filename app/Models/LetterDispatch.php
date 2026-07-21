<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterDispatch extends Model
{
    protected $fillable = ['letter_template_id', 'sent_by', 'recipient_count', 'sent_at'];
    protected $casts = ['sent_at' => 'datetime'];

    public function template()
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function recipients()
    {
        return $this->hasMany(LetterDispatchRecipient::class, 'dispatch_id');
    }
}
