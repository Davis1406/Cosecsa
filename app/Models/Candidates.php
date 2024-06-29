<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Candidates extends Model
{
    use HasFactory;

    protected $table = 'candidates';

    protected $fillable = [
        'user_id',
        'firstname',
        'middlename',
        'lastname',
        'personal_email',
        'gender',
        'programme_id',
        'hospital_id',
        'country_id',
        'repeat_paper_one',
        'repeat_paper_two',
        'mmed',
        'entry_number',
        'admission_year',
        'exam_year',
        'invoice_number',
        'invoice_date',
        'invoice_status',
        'sponsor',
        'amount_paid',
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
