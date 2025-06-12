<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminerRole extends Model
{
    protected $table = 'examiners_roles';
    
    protected $fillable = [
        'role'
    ];

    public function examiners()
    {
        return $this->hasMany(ExamsModel::class, 'role_id');
    }
}
