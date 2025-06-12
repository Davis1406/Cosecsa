<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminerGroup extends Model
{
    use HasFactory;
    protected $table = 'examiners_groups';
    
    protected $fillable = [
        'group_name'
    ];

    public function examiners()
    {
        return $this->belongsToMany(ExamsModel::class, 'exams_groups', 'group_id', 'examiner_id')
                    ->withPivot('year_id')
                    ->withTimestamps();
    }
}
