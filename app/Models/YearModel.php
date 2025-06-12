<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearModel extends Model
{
    use HasFactory;
    
     protected $table = 'years';
    
    protected $fillable = [
        'year_name'
    ];

    public function examinerGroups()
    {
        return $this->hasMany(\App\Models\ExamsGroup::class, 'year_id');
    }

    public function examinerShifts()
    {
        return $this->hasMany(\App\Models\ExamsShift::class, 'year_id');
    }
}
