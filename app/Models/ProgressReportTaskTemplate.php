<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReportTaskTemplate extends Model
{
    protected $fillable = ['user_id', 'activity_description', 'default_planned_activities', 'is_active', 'sort_order', 'created_by'];
    protected $casts = ['is_active' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
