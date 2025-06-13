<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatesFormModel extends Model
{
    use HasFactory;

    protected $table = 'mcs_results';

    protected $fillable = [
        'candidate_id',
        'examiner_id',
        'station_id',
        'group_id',
        'total',
        'overall',
        'question_mark',
        'remarks'
    ];
}

