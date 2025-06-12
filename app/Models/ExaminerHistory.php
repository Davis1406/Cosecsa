<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminerHistory extends Model
{
    use HasFactory;

    protected $table = 'examiners_history';

    protected $fillable = [
        'exm_id',
        'exam_availability',
        'virtual_mcs_participated',
        'fcs_participated',
        'hospital_type',
        'hospital_name',
        'examination_years',
    ];

    protected $casts = [
        'exam_availability' => 'array',
        'examination_years' => 'array',
    ];

    // Relationships

    public function examiner()
    {
        return $this->belongsTo(ExamsModel::class, 'exm_id');
    }

    public function role()
    {
        return $this->belongsTo(ExaminerRole::class, 'role_id');
    }
}
