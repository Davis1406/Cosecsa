<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'last_read_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
