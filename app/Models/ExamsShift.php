<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamsShift extends Model
{
    use HasFactory;
    protected $table = 'exams_shifts';
    
    protected $fillable = [
        'exm_id',
        'shift',
        'year_id'
    ];

    public function examiner()
    {
        return $this->belongsTo(\App\Models\ExamsModel::class, 'exm_id');
    }

    public function year()
    {
        return $this->belongsTo(YearModel::class, 'year_id');
    }
}
