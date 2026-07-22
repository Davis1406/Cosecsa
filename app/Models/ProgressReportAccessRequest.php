<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReportAccessRequest extends Model
{
    protected $fillable = ['participant_id', 'requested_by', 'status', 'reason', 'decided_by', 'decided_at'];
    protected $casts = ['decided_at' => 'datetime'];

    public function participant()
    {
        return $this->belongsTo(ProgressReportParticipant::class, 'participant_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
