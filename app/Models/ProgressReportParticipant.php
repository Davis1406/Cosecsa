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

    // A section locks once its period is no longer the current reporting
    // month, or once the period's own due date has passed — NOT simply
    // because it was submitted. Staff can freely keep editing/resubmitting
    // right up to the deadline even after an earlier submission; only past
    // the deadline does further editing require the Administrative
    // Officer to grant a fresh edit request (cleared on the next submit).
    public function isLocked(): bool
    {
        if ($this->edit_unlocked) {
            return false;
        }

        if (! $this->period->is_current) {
            return true;
        }

        return now()->startOfDay()->gt($this->period->due_date);
    }
}
