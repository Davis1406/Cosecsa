<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReportPeriod extends Model
{
    protected $fillable = ['period_month', 'due_date', 'status', 'is_current', 'reminder_sent_at', 'consolidated_at', 'consolidated_by', 'created_by'];
    protected $casts = ['period_month' => 'date', 'due_date' => 'date', 'is_current' => 'boolean', 'reminder_sent_at' => 'datetime', 'consolidated_at' => 'datetime'];

    public function participants()
    {
        return $this->hasMany(ProgressReportParticipant::class, 'period_id')->orderBy('sort_order');
    }

    public function tasks()
    {
        return $this->hasMany(ProgressReportTask::class, 'period_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function consolidator()
    {
        return $this->belongsTo(User::class, 'consolidated_by');
    }
}
