<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReportParticipant extends Model
{
    protected $fillable = ['period_id', 'user_id', 'section_label', 'status', 'submitted_at', 'sort_order'];
    protected $casts = ['submitted_at' => 'datetime'];

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
}
