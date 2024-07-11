<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Request;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($password)
    {
        if ($this->user_type == 1) {
            $this->attributes['password'] = bcrypt($password);
        } else {
            $this->attributes['password'] = $password;
        }
    }

    static public function getAdmin()
    {
        $return = self::select('users.*')
            ->where('user_type', '1')
            ->where('is_deleted', '=', '0');
        
        if (!empty(Request::get('email'))) {
            $return = $return->where('email', 'like', '%' . Request::get('email') . '%');
        }

        if (!empty(Request::get('name'))) {
            $return = $return->where('name', 'like', '%' . Request::get('name') . '%');
        }

        if (!empty(Request::get('date'))) {
            $return = $return->whereDate('created_at', 'like', '%' . Request::get('date') . '%');
        }

        $return = $return->orderBy('id', 'asc')->paginate(10);
        return $return;
    }

    static public function getTrainee()
    {
        $return = self::select(
                'users.id as user_id',
                'users.name as name',
                'users.email as user_email',
                'users.password as user_password',
                'trainees.id as trainee_id',
                'trainees.user_id as t_id',
                'trainees.*',
                'hospitals.name as hospital_name',
                'programmes.name as programme_name',
                'countries.country_name as country_name'
            )
            ->join('trainees', 'users.id', '=', 'trainees.user_id')
            ->join('hospitals', 'trainees.hospital_id', '=', 'hospitals.id')
            ->join('programmes', 'trainees.programme_id', '=', 'programmes.id')
            ->join('countries', 'trainees.country_id', '=', 'countries.id')
            ->where('users.user_type', '2')
            ->where('users.is_deleted', '=', '0');

        $return = $return->orderBy('trainee_id', 'asc')->get();
        
        return $return;
    }

    static public function getCandidates()    
    {
        $return = self::select(
                'users.id as user_id',
                'users.name as name',
                'users.email as user_email',
                'users.password as user_password',
                'users.user_type as user_type',
                'candidates.id as candidate_id',
                'candidates.user_id as c_id',
                'candidates.*',
                'hospitals.name as hospital_name',
                'programmes.name as programme_name',
                'countries.country_name as country_name'
            )
            ->join('candidates', 'users.id', '=', 'candidates.user_id')
            ->leftJoin('hospitals', 'candidates.hospital_id', '=', 'hospitals.id')
            ->leftJoin('programmes', 'candidates.programme_id', '=', 'programmes.id')
            ->leftJoin('countries', 'candidates.country_id', '=', 'countries.id')
            ->whereIn('users.id', function($query) {
                $query->select('user_id')
                      ->from('candidates');
            })
            ->where('users.is_deleted', '=', '0');
    
        $return = $return->orderBy('candidate_id', 'asc')->get();
        
        return $return;
    }

    static public function getTrainers()    
    {
        $return = self::select(
                'users.id as user_id',
                'users.name as name',
                'users.email as user_email',
                'users.password as user_password',
                'users.user_type as user_type',
                'trainers.id as trainer_id', 
                'trainers.user_id as tr_id',
                'trainers.*',
                'hospitals.name as hospital_name',
                'countries.country_name as country_name'

            )
            ->join('trainers', 'users.id', '=', 'trainers.user_id')
            ->leftJoin('hospitals', 'trainers.hospital_id', '=', 'hospitals.id')
            ->leftJoin('countries', 'hospitals.country_id', '=', 'countries.id')
            ->whereIn('users.id', function($query) {
                $query->select('user_id')
                      ->from('trainers');
            })
            ->where('users.is_deleted', '=', '0');
    
        $return = $return->orderBy('trainer_id', 'asc')->get();
        
        return $return;
    }


    static public function getReps()    
    {
        $return = self::select(
                'users.id as user_id',
                'users.name as name',
                'users.email as user_email',
                'users.password as user_password',
                'users.user_type as user_type',
                'country_reps.id as reps_id',
                'country_reps.user_id as cr_id',
                'country_reps.*',
                'countries.country_name as country_name'

            )
            ->join('country_reps', 'users.id', '=', 'country_reps.user_id')
            ->leftJoin('countries', 'country_reps.country_id', '=', 'countries.id')
            ->whereIn('users.id', function($query) {
                $query->select('user_id')
                      ->from('country_reps');
            })
            ->where('users.is_deleted', '=', '0');
    
        $return = $return->orderBy('reps_id', 'asc')->get();
        
        return $return;
    }


    static public function getEmailSingle($email)
    {
        return self::where('email', '=', $email)->first();
    }

    static public function getSingleToken($remember_token)
    {
        return self::where('remember_token', '=', $remember_token)->first();
    }

    static public function getSingleId($id)
    {
        return self::find($id);
    }
}
