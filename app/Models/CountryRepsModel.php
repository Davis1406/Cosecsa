<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryRepsModel extends Model
{
    use HasFactory;

    protected $table = 'country_reps';

    protected $fillable = [
        'user_id',
        'profile_image',
        'cosecsa_email',
        'country_id',
        'mobile_no',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
