<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReportTaskRevision extends Model
{
    public $timestamps = false;
    protected $fillable = ['task_id', 'editor_id', 'old_values', 'new_values', 'created_at'];
    protected $casts = ['old_values' => 'array', 'new_values' => 'array', 'created_at' => 'datetime'];

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
