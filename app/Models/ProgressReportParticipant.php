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

    // A section is locked once submitted, or once its period is no longer
    // the current reporting month (a pending section on a past period is
    // just as stale as a submitted one) — until the Administrative Officer
    // grants a fresh edit request. Approval is cleared on the next submit.
    public function isLocked(): bool
    {
        if ($this->edit_unlocked) {
            return false;
        }

        return $this->status === 'submitted' || ! $this->period->is_current;
    }
}
