<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class MembersModel extends Model
{
    use HasFactory;

    protected $table = 'members';

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
        'country_id',
        'is_deleted',
        'phone_number',
        'is_promoted',
        'address',
        'admission_year',
        'membership_year'
    ];

    static public function getMembers()
    {
        $return = self::select(
                'members.*',

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
