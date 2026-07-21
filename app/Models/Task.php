<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'conversation_id', 'created_by', 'assigned_to', 'title', 'description', 'due_date', 'status',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
