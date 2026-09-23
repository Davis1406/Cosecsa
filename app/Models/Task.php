<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'conversation_id', 'created_by', 'assigned_to', 'title', 'description', 'due_date', 'status', 'read_at',
    ];
    protected $casts = ['read_at' => 'datetime'];

    // The assignee has "read" a task once they open it, view its
    // conversation, or change its status (see TaskController / MessagingController).
    public static function markReadFor(int $userId, $taskIds = null, $conversationId = null): void
    {
        static::where('assigned_to', $userId)->whereNull('read_at')
            ->when($taskIds !== null, fn ($q) => $q->whereIn('id', (array) $taskIds))
            ->when($conversationId !== null, fn ($q) => $q->where('conversation_id', $conversationId))
            ->update(['read_at' => now()]);
    }

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
