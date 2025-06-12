<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamsGroup extends Model
{
    use HasFactory;

    protected $table = 'exams_groups';
    
    protected $fillable = [
        'exm_id',
        'group_id',
        'year_id'
    ];

    public function examiner()
    {
        return $this->belongsTo(\App\Models\ExamsModel::class, 'examiner_id');
    }

    public function group()
    {
        return $this->belongsTo(ExaminerGroup::class, 'group_id');
    }

    public function year()
    {
        return $this->belongsTo(YearModel::class, 'year_id');
    }
}
