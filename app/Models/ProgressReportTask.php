<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReportTask extends Model
{
    protected $fillable = [
        'period_id', 'participant_id', 'template_id', 'row_no',
        'activity_description', 'planned_activities', 'current_status', 'next_steps', 'updated_by',
    ];

    public function participant()
    {
        return $this->belongsTo(ProgressReportParticipant::class, 'participant_id');
    }

    public function period()
    {
        return $this->belongsTo(ProgressReportPeriod::class, 'period_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function revisions()
    {
        return $this->hasMany(ProgressReportTaskRevision::class, 'task_id')->orderByDesc('created_at');
    }
}
