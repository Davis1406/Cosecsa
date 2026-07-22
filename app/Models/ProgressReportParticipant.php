<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReportParticipant extends Model
{
    protected $fillable = ['period_id', 'user_id', 'section_label', 'status', 'submitted_at', 'edit_unlocked', 'sort_order'];
    protected $casts = ['submitted_at' => 'datetime', 'edit_unlocked' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function period()
    {
        return $this->belongsTo(ProgressReportPeriod::class, 'period_id');
    }

    public function tasks()
    {
        return $this->hasMany(ProgressReportTask::class, 'participant_id')->orderBy('row_no');
    }

    public function accessRequests()
    {
        return $this->hasMany(ProgressReportAccessRequest::class, 'participant_id');
    }

    public function pendingAccessRequest()
    {
        return $this->hasOne(ProgressReportAccessRequest::class, 'participant_id')->where('status', 'pending')->latestOfMany();
    }

    // A section is locked once submitted, until the Administrative Officer
    // grants a fresh edit request; approval is cleared on the next submit.
    public function isLocked(): bool
    {
        return $this->status === 'submitted' && ! $this->edit_unlocked;
    }
}
