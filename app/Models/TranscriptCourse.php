<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptCourse extends Model
{
    protected $fillable = [
        'transcript_record_id', 'section', 'subsection', 'course_name',
        'academic_year', 'result', 'sort_order',
    ];
}
