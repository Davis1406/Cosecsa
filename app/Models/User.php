<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
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
                'countries.country_name as country_name',
                'study_year.name as programme_year'
            )
            ->join('trainees', 'users.id', '=', 'trainees.user_id')
            ->join('hospitals', 'trainees.hospital_id', '=', 'hospitals.id')
            ->join('programmes', 'trainees.programme_id', '=', 'programmes.id')
            ->join('study_year', 'trainees.training_year', '=', 'study_year.id')
            ->join('countries', 'trainees.country_id', '=', 'countries.id')
            ->where('users.user_type', '2')
            ->where('users.is_deleted', '=', '0');

        $return = $return->orderBy('trainee_id', 'asc')->get();
        
        return $return;
    }


    static public function getFellows()
    {
        $return = self::select(
                'users.id as user_id',
                'users.name as fellow_name',
                'users.email as email',
                'users.password as user_password',
                'fellows.id as fellow_id',
                'fellows.user_id as f_id',
                'fellows.personal_email as personal_email',
                'fellows.*',
                'programmes.name as programme_name',
                'countries.country_name as country_name',
                'categories.category_name as fellowship_type'

        )
            ->join('fellows', 'users.id', '=', 'fellows.user_id')
            ->leftJoin('programmes', 'fellows.programme_id', '=', 'programmes.id')
            ->leftJoin('categories', 'fellows.category_id', '=', 'categories.id')
            ->leftJoin('countries', 'fellows.country_id', '=', 'countries.id')
            ->where('users.user_type', '7')
            ->where('users.is_deleted', '=', '0');

        $return = $return->orderBy('id', 'asc')->get();
        
        return $return;

    }
    


    static public function getMembers()
    {
        $return = self::select(
                'users.id as user_id',
                'users.name as member_name',
                'users.email as email',
                'users.password as user_password',
                'members.id as members_id',
                'members.user_id as m_id',
                'members.personal_email as personal_email',
                'members.*',
                // 'programmes.name as programme_name',
                'countries.country_name as country_name',
                'categories.category_name as membership_type'

            )
            ->join('members', 'users.id', '=', 'members.user_id')
            // ->join('programmes', 'fellows.programme_id', '=', 'programmes.id')
            ->leftJoin('categories', 'members.category_id', '=', 'categories.id')
            ->leftJoin('countries', 'members.country_id', '=', 'countries.id')
            ->where('users.user_type', '8')
            ->where('users.is_deleted', '=', '0');

        $return = $return->orderBy('id', 'asc')->get();
        
        return $return;

    }

    static public function getExaminers()
    {
        $return = self::select(
                'users.id as user_id',
                'users.name as examiner_name',
                'users.email as email',
                'users.password as examiner_password',
                'examiners.id as examin_id',
                'examiners.user_id as ex_id',
                'examiners.*',
                'examiners_groups.group_name as group_name',
                'countries.country_name as country_name'

        )
            ->join('examiners', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('examiners_groups', 'examiners.group_id', '=', 'examiners_groups.id')
            ->leftJoin('countries', 'examiners.country_id', '=', 'countries.id')
            ->where('users.user_type', '9')
            ->where('users.is_deleted', '=', '0');

        $return = $return->orderBy('id', 'asc')->get();
        
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
                'candidates.id as candidates_id',
                'candidates.user_id as c_id',
                'candidates.*',
                'hospitals.name as hospital_name',
                'programmes.name as programme_name',
                'examiners_groups.group_name as group_name',
                'countries.country_name as country_name'
            )
            ->join('candidates', 'users.id', '=', 'candidates.user_id')
            ->leftJoin('hospitals', 'candidates.hospital_id', '=', 'hospitals.id')
            ->leftJoin('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
            ->leftJoin('programmes', 'candidates.programme_id', '=', 'programmes.id')
            ->leftJoin('countries', 'candidates.country_id', '=', 'countries.id')
            ->whereIn('users.id', function($query) {
                $query->select('user_id')
                      ->from('candidates');
            })
            ->where('users.is_deleted', '=', '0');
    
        $return = $return->orderBy('candidates_id', 'asc')->get();
        
        return $return;
    }


    //***************************This function is no Longer used.*************************
    static public function getexaminerCandidates()
{
    $examinerGroupId = \DB::table('examiners')
        ->where('user_id', Auth::id())
        ->value('group_id');

    // Fetch candidates with the same group_id as the examiner
    $candidates = self::select(
            'users.id as user_id',
            'users.name as name',
            'users.email as user_email',
            'users.user_type as user_type',
            'candidates.id as candidates_id',
            'candidates.user_id as c_id',
            'candidates.group_id as candidate_group_id',
            'candidates.*',
            'hospitals.name as hospital_name',
            'programmes.name as programme_name',
            'examiners_groups.group_name as group_name',
            'countries.country_name as country_name'
        )
        ->join('candidates', 'users.id', '=', 'candidates.user_id')
        ->leftJoin('hospitals', 'candidates.hospital_id', '=', 'hospitals.id')
        ->leftJoin('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
        ->leftJoin('programmes', 'candidates.programme_id', '=', 'programmes.id')
        ->leftJoin('countries', 'candidates.country_id', '=', 'countries.id')
        ->where('candidates.group_id', '=', $examinerGroupId)
        ->where('users.is_deleted', '=', '0')
        ->orderBy('candidates_id', 'asc')
        ->get();

    return $candidates; // Returns a collection of candidates
}


static public function getCandidatesByGroup($groupId)
{
    return self::select(
            'users.id as user_id',
            'users.name as name',
            'candidates.id as cand_id',
            'candidates.candidate_id as c_id'
        )
        ->join('candidates', 'users.id', '=', 'candidates.user_id')
        ->where('candidates.group_id', '=', $groupId)
        ->where('users.is_deleted', '=', '0')
        ->orderBy('candidates.candidate_id', 'asc')
        ->get();
}


//Examination Results Examiners Sides -- for EXaminer Results PAGE
public static function getExaminationResults()
{
    // Retrieve the examiner ID linked to the logged-in user
    $examinerId = \DB::table('examiners')
        ->where('user_id', auth()->id())
        ->value('id');

    if (!$examinerId) {
        return collect(); 
    }

    // Fetch the most recent submission from `examination_form`
    $lastExamFormSubmission = \DB::table('examination_form')
        ->where('examiner_id', $examinerId)
        ->latest('created_at')
        ->first();

    // Fetch the most recent submission from `gs_form`
    $lastGsFormSubmission = \DB::table('gs_form')
        ->where('examiner_id', $examinerId)
        ->latest('created_at')
        ->first();

    // Determine the last submitted form
    $lastSubmittedForm = null;

    if ($lastExamFormSubmission && $lastGsFormSubmission) {
        $lastSubmittedForm = $lastExamFormSubmission->created_at > $lastGsFormSubmission->created_at
            ? 'examination_form'
            : 'gs_form';
    } elseif ($lastExamFormSubmission) {
        $lastSubmittedForm = 'examination_form';
    } elseif ($lastGsFormSubmission) {
        $lastSubmittedForm = 'gs_form';
    }

    // Fetch records from `examination_form`
    $examinationFormResults = \DB::table('examination_form')
        ->select(
            'examination_form.*',
            'examination_form.id as record_id',
            'examination_form.candidate_id',
            'examination_form.station_id',
            'examination_form.group_id',
            'examination_form.total as total_marks',
            'examination_form.overall as grade', // Column in `examination_form`
            'examination_form.remarks',
            'candidates.candidate_id as candidate_name',
            'examiners_groups.group_name as group_name',
            \DB::raw("'examination_form' as source_table") // Distinguish source
        )
        ->join('candidates', 'examination_form.candidate_id', '=', 'candidates.id')
        ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
        ->where('examination_form.examiner_id', $examinerId);

    // Fetch records from `gs_form`
    $gsFormResults = \DB::table('gs_form')
        ->select(
            'gs_form.*',
            'gs_form.id as record_id',
            'gs_form.candidate_id',
            'gs_form.station_id',
            'gs_form.group_id as g_id',
            'gs_form.total as total_marks',
            \DB::raw('NULL as grade'), // Placeholder for missing `overall` column
            'gs_form.remarks',
            'candidates.candidate_id as candidate_name',
            'examiners_groups.group_name as group_name',
            \DB::raw("'gs_form' as source_table") // Distinguish source
        )
        ->join('candidates', 'gs_form.candidate_id', '=', 'candidates.id')
        ->join('examiners_groups', 'gs_form.group_id', '=', 'examiners_groups.id')
        ->where('gs_form.examiner_id', $examinerId);

    // Combine results with UNION and order by ID
    $combinedResults = $examinationFormResults
        ->union($gsFormResults)
        ->orderBy('record_id', 'asc')
        ->get();

    return [
        'lastSubmittedForm' => $lastSubmittedForm,
        'records' => $combinedResults,
    ];
}


public static function getAdminExamsResults()
{
    return \DB::table('examination_form')
        ->select(
            'examination_form.candidate_id as cand_id', // Use "cand_id" for consistency
            'candidates.firstname',
            'candidates.middlename',
            'candidates.lastname',
            'candidates.candidate_id as c_id', 
            'examination_form.station_id',
            'examination_form.total'
        )
        ->join('candidates', 'examination_form.candidate_id', '=', 'candidates.id')
        ->orderBy('examination_form.candidate_id')
        ->get()
        ->groupBy('cand_id')
        ->map(function ($group) {
            $candidate = $group->first();
            return (object) [
                'candidate_id' => $candidate->c_id, // Use "c_id" as mapped in select clause
                'cnd_id' => $candidate-> cand_id,
                'fullname' => "{$candidate->firstname} {$candidate->middlename} {$candidate->lastname}",
                'stations' => $group->map(function ($row) {
                    return [
                        'station_id' => $row->station_id,
                        'total' => $row->total
                    ];
                })->toArray(),
            ];
        });
}

// Get Results for GS
public static function getGsResults()
{
    return \DB::table('gs_form')
        ->select(
            'gs_form.candidate_id as cand_id',
            'candidates.firstname',
            'candidates.middlename',
            'candidates.lastname',
            'candidates.candidate_id as c_id',
            'gs_form.station_id',
            \DB::raw('SUM(gs_form.total) as total_sum') // Sum totals grouped by station_id
        )
        ->join('candidates', 'gs_form.candidate_id', '=', 'candidates.id')
        ->groupBy('gs_form.candidate_id', 'gs_form.station_id', 'candidates.firstname', 'candidates.middlename', 'candidates.lastname', 'candidates.candidate_id')
        ->orderBy('gs_form.candidate_id')
        ->get()
        ->groupBy('cand_id')
        ->map(function ($group) {
            $candidate = $group->first();
            return (object) [
                'candidate_id' => $candidate->c_id,
                'cnd_id' => $candidate->cand_id,
                'fullname' => "{$candidate->firstname} {$candidate->middlename} {$candidate->lastname}",
                'stations' => $group->map(function ($row) {
                    return [
                        'station_id' => $row->station_id,
                        'total_sum' => $row->total_sum // Return summed total for each station
                    ];
                })->toArray(),
            ];
        });
}

// Trainers Function
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
