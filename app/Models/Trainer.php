<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    use HasFactory;

    protected $table = 'trainers';

    protected $fillable = [
        'user_id',
        'hospital_id',
        'phone_number',
        'profile_image',
        'assistant_pd',
        'assistant_email',
        'mobile_no',
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
