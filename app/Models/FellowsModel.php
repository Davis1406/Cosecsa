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
        'second_email',
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
        'fellowship_year',
        // Extended fields
        'candidate_number',
        'supervised_by',
        'registered_by',
        'secretariat_registration_date',
        'prog_entry_fee_year',
        'prog_entry_mode_payment',
        'exam_fee_year',
        'exam_fee_date_paid',
        'exam_fee_mode_payment',
        'exam_fee_amount_paid',
        'exam_fee_payment_verified',
        'sponsored_by',
        'mcs_qualification_year',
        'country_mcs_training',
        'exam_year_upcoming',
        'exam_year_previous',
        'cosecsa_region',
    ];

    public function labels()
    {
        return $this->belongsToMany(FellowLabel::class, 'fellow_label_assignments', 'fellow_id', 'label_id');
    }

    static public function getFellows()
    {
        $return = self::select('fellows.*')
            ->get();

        return $return;
    }
}
