<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FellowsModel extends Model
{
    use HasFactory;

    protected $table = 'fellows';

    protected $fillable = [
        'user_id',
        'category_id',
        'firstname',
        'middlename',
        'lastname',
        'personal_email',
        'gender',
        'status',
        'profile_image',
        'programme_id',
        'country_id',
        'phone_number',
        'is_promoted',
        'address',
        'current_specialty',
        'organization',
        'admission_year',
        'fellowship_year'
    ];

    static public function getFellows()
    {
        $return = self::select(
                'fellows.*',

                // 'programmes.name as category',
                // 'study_year.id as study_year_id',
                // 'study_year.name as programme_year'
            )
            // ->join('study_year', 'programmes.id', '=', 'study_year.programme_id')
            // ->orderBy('programmes.id', 'asc')
            ->get();

        return $return;
    }
}
