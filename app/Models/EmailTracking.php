<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTracking extends Model
{
    protected $table = 'email_tracking';

    protected $fillable = [
        'token',
        'exm_id',
        'recipient_email',
        'subject',
        'sent_at',
        'opened_at',
        'open_count',
        'last_ip',
        'last_user_agent',
    ];

    protected $casts = [
        'sent_at'   => 'datetime',
        'opened_at' => 'datetime',
    ];
}
