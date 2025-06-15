<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamsModel extends Model
{
    use HasFactory;

    protected $table = 'examiners';
    
    protected $fillable = [
        'mobile',
        'role_id',
        'gender',
        'country_id',
        'user_id',
        'examiner_id',
        'specialty',
        'subspecialty',
        'curriculum_vitae',
        'passport_image'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(\App\Models\Country::class, 'country_id');
    }

    public function role()
    {
        return $this->belongsTo(ExaminerRole::class, 'role_id');
    }

    // Many-to-many relationship with groups through pivot table
    public function groups()
    {
        return $this->belongsToMany(ExaminerGroup::class, 'exams_groups', 'exm_id', 'group_id')
                    ->withPivot('year_id')
                    ->withTimestamps();
    }

    // One-to-many relationship with shifts
    public function shifts()
    {
        return $this->hasMany(ExamsShift::class, 'exm_id');
    }

    // Get current groups for a specific year
    public function getCurrentGroups($yearId = null)
    {
        $query = $this->groups();
        if ($yearId) {
            $query->wherePivot('year_id', $yearId);
        }
        return $query;
    }

    // Get current shifts for a specific year
    public function getCurrentShifts($yearId = null)
    {
        $query = $this->shifts();
        if ($yearId) {
            $query->where('year_id', $yearId);
        }
        return $query;
  }

}