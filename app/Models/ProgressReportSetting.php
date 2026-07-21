<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReportSetting extends Model
{
    protected $fillable = ['due_day', 'reminder_days_before', 'reminder_enabled', 'updated_by'];
    protected $casts = ['reminder_enabled' => 'boolean'];

    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }
}
