<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamsModel extends Model
{
    use HasFactory;


    protected $table = 'examiners';

    protected $fillable = [
        'user_id',
        'country_id',
        'examiner_id',
        'group_id',
        'mobile',
        'specialty',
        'shift'
    ];
}
