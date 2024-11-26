<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSurgery extends Model
{
    protected $table = 'gs_form';

    protected $fillable = [
        'candidate_id',
        'examiner_id',
        'station_id',
        'group_id',
        'total',
        'overall',
        'question_mark',
        'remarks',
        'exam_year'
    ];
}
