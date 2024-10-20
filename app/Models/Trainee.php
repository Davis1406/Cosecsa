<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainee extends Model
{
    use HasFactory;

    protected $table = 'trainees';

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
        'hospital_id',
        'country_id',
        'entry_number',
        'admission_letter_status',
        'invitation_letter_status',
        'admission_year',
        'exam_year',
        'training_year',
        'programme_period',
        'invoice_number',
        'invoice_date',
        'invoice_status',
        'sponsor',
        'mode_of_payment',
        'amount_paid',
        'payment_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hospital()
    {
        return $this->belongsTo(HospitalModel::class, 'hospital_id');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
