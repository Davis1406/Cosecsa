<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Formerly Trainer — see ProgrammeDirectorController. Not queried directly
// (this app reads via ApiClient, not MySQL — see CLAUDE.md); kept only for
// parity/reference the way the old Trainer model was.
class ProgrammeDirector extends Model
{
    use HasFactory;

    protected $table = 'programme_directors';

    protected $fillable = [
        'user_id',
        'hospital_id',
        'programme_id',
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
