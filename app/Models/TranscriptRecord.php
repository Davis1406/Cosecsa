<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptRecord extends Model
{
    protected $fillable = [
        'user_id', 'template_id', 'full_name', 'gender', 'programme_entry_number',
        'medium_of_instruction', 'programme', 'entry_period', 'completion_period',
        'final_score', 'created_by',
    ];

    public function courses()
    {
        return $this->hasMany(TranscriptCourse::class)->orderBy('sort_order');
    }

    public function template()
    {
        return $this->belongsTo(TranscriptTemplate::class, 'template_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
