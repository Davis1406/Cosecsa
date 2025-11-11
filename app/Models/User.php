<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;



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

    // Helper method to get current year ID
    static public function getCurrentYearId()
    {
        return \DB::table('years')
            ->where('year_name', date('Y'))
            ->value('id');
    }

    static public function getAdmin()
    {
        $return = self::select('users.*')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->where('users.user_type', '1')
            ->where('users.is_deleted', '=', '0')
            ->where('user_roles.is_active', 1);

        if (!empty(Request::get('email'))) {
            $return = $return->where('users.email', 'like', '%' . Request::get('email') . '%');
        }

        if (!empty(Request::get('name'))) {
            $return = $return->where('users.name', 'like', '%' . Request::get('name') . '%');
        }

        if (!empty(Request::get('date'))) {
            $return = $return->whereDate('users.created_at', 'like', '%' . Request::get('date') . '%');
        }

        return $return->orderBy('users.id', 'asc')->paginate(10);
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
            ->join('user_roles', function ($join) {
                $join->on('user_roles.user_id', '=', 'users.id')
                    ->where('user_roles.is_active', '=', 1);
            })
            ->where('users.user_type', '2')
            ->orderBy('trainee_id', 'asc')
            ->get();

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
            ->join('user_roles', function ($join) {
                $join->on('user_roles.user_id', '=', 'users.id')
                    ->where('user_roles.is_active', '=', 1);
            })
            ->where('users.user_type', '7');

        return $return->orderBy('id', 'asc')->get();
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
            'countries.country_name as country_name',
            'categories.category_name as membership_type'
        )
            ->join('members', 'users.id', '=', 'members.user_id')
            ->leftJoin('categories', 'members.category_id', '=', 'categories.id')
            ->leftJoin('countries', 'members.country_id', '=', 'countries.id')
            ->join('user_roles', function ($join) {
                $join->on('user_roles.user_id', '=', 'users.id')
                    ->where('user_roles.is_active', '=', 1);
            })
            ->where('users.user_type', '8');

        return $return->orderBy('id', 'asc')->get();
    }


    static public function getSingleExaminer($userId, $yearId = null)
    {
        if (!$yearId) {
            $yearId = self::getCurrentYearId();
        }

        $examiner = self::select(
            'users.id as user_id',
            'users.name as examiner_name',
            'users.email as email',
            'users.password as examiner_password',
            'examiners.id as examin_id',
            'examiners.user_id as ex_id',
            'examiners.*',
            'countries.country_name as country_name',
            'examiners_roles.role as examiner_role'
        )
            ->join('examiners', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('countries', 'examiners.country_id', '=', 'countries.id')
            ->leftJoin('examiners_roles', 'examiners.role_id', '=', 'examiners_roles.id')
            ->join('user_roles', function ($join) {
                $join->on('user_roles.user_id', '=', 'users.id')
                    ->where('user_roles.is_active', '=', 1);
            })
            ->where('users.id', $userId)
            ->where('users.user_type', '9')
            ->first();

        if (!$examiner) {
            return null;
        }

        $groups = \DB::table('exams_groups')
            ->join('examiners_groups', 'exams_groups.group_id', '=', 'examiners_groups.id')
            ->where('exams_groups.exm_id', $examiner->examin_id)
            ->where('exams_groups.year_id', $yearId)
            ->select('examiners_groups.id as group_id', 'examiners_groups.group_name', 'exams_groups.year_id')
            ->get();

        $shifts = \DB::table('exams_shifts')
            ->where('exm_id', $examiner->examin_id)
            ->where('year_id', $yearId)
            ->select('shift', 'year_id')
            ->get();

        $history = \App\Models\ExaminerHistory::where('exm_id', $examiner->examin_id)->first();

        $examiner->groups = $groups;
        $examiner->shifts = $shifts;
        $examiner->history = $history;

        $examiner->group_name = $groups->isNotEmpty() ? $groups->first()->group_name : null;
        $examiner->group_id = $groups->isNotEmpty() ? $groups->first()->group_id : null;

        $examiner->shift_id = $shifts->isNotEmpty() ? $shifts->first()->shift : null;
        $examiner->shift = $shifts->isNotEmpty() ? self::getShiftName($shifts->first()->shift) : null;

        if ($history) {
            $examiner->virtual_mcs_participated = $history->virtual_mcs_participated;
            $examiner->fcs_participated = $history->fcs_participated;
            $examiner->participation_type = $history->role ? $history->role->role : null;
            $examiner->hospital_type = $history->hospital_type;
            $examiner->hospital_name = $history->hospital_name;
            $examiner->examination_years = $history->examination_years;
            $examiner->exam_availability = $history->exam_availability;
        }

        if ($groups->count() > 1) {
            $examiner->group_name = $groups->pluck('group_name')->implode(', ');
        }

        if ($shifts->count() > 1) {
            $examiner->shift_id = $shifts->pluck('shift')->implode(', ');
            $examiner->shift = $shifts->map(function ($shift) {
                return self::getShiftName($shift->shift);
            })->implode(', ');
        }

        return $examiner;
    }


    static public function getExaminers($yearId = null)
    {
        if (!$yearId) {
            $yearId = self::getCurrentYearId();
        }

        $query = self::select(
            'users.id as user_id',
            'users.name as examiner_name',
            'users.email as email',
            'users.password as examiner_password',
            'examiners.id as examin_id',
            'examiners.user_id as ex_id',
            'examiners.*',
            'countries.country_name as country_name',
            'examiners_roles.role as examiner_role'
        )
            ->join('examiners', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('countries', 'examiners.country_id', '=', 'countries.id')
            ->leftJoin('examiners_roles', 'examiners.role_id', '=', 'examiners_roles.id')
            ->join('user_roles', function ($join) {
                $join->on('user_roles.user_id', '=', 'users.id')
                    ->where('user_roles.is_active', '=', 1);
            })
            ->where('users.user_type', '9');

        $examiners = $query->orderBy('users.id', 'asc')->get();

        $examiners->each(function ($examiner) use ($yearId) {
            $groups = \DB::table('exams_groups')
                ->join('examiners_groups', 'exams_groups.group_id', '=', 'examiners_groups.id')
                ->where('exams_groups.exm_id', $examiner->examin_id)
                ->where('exams_groups.year_id', $yearId)
                ->select('examiners_groups.id as group_id', 'examiners_groups.group_name', 'exams_groups.year_id')
                ->get();

            $shifts = \DB::table('exams_shifts')
                ->where('exm_id', $examiner->examin_id)
                ->where('year_id', $yearId)
                ->select('shift', 'year_id')
                ->get();

            $history = \App\Models\ExaminerHistory::where('exm_id', $examiner->examin_id)->first();

            $examiner->groups = $groups;
            $examiner->shifts = $shifts;
            $examiner->history = $history;

            $examiner->group_name = $groups->isNotEmpty() ? $groups->first()->group_name : null;
            $examiner->group_id = $groups->isNotEmpty() ? $groups->first()->group_id : null;

            $examiner->shift_id = $shifts->isNotEmpty() ? $shifts->first()->shift : null;
            $examiner->shift = $shifts->isNotEmpty() ? self::getShiftName($shifts->first()->shift) : null;

            if ($history) {
                $examiner->virtual_mcs_participated = $history->virtual_mcs_participated;
                $examiner->fcs_participated = $history->fcs_participated;
                $examiner->participation_type = $history->role ? $history->role->role : null;
                $examiner->hospital_type = $history->hospital_type;
                $examiner->hospital_name = $history->hospital_name;
                $examiner->examination_years = $history->examination_years;
            }

            if ($groups->count() > 1) {
                $examiner->group_name = $groups->pluck('group_name')->implode(', ');
            }

            if ($shifts->count() > 1) {
                $examiner->shift_id = $shifts->pluck('shift')->implode(', ');
                $examiner->shift = $shifts->map(function ($shift) {
                    return self::getShiftName($shift->shift);
                })->implode(', ');
            }
        });

        return $examiners;
    }


    // Updated getShiftName method to match your select options
    public static function getShiftName($shiftId)
    {
        $shiftNames = [
            1 => 'Morning',
            2 => 'Morning & Afternoon',
            3 => 'Afternoon',
        ];

        return $shiftNames[$shiftId] ?? 'Unknown Shift';
    }

    static public function getCandidates()
    {
        $currentYear = date('Y');

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
            'countries.country_name as country_name',
            'user_roles.role_type as role_type'
        )
            ->leftJoin('candidates', 'users.id', '=', 'candidates.user_id') // ✅ changed to LEFT JOIN
            ->leftJoin('hospitals', 'candidates.hospital_id', '=', 'hospitals.id')
            ->leftJoin('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
            ->leftJoin('programmes', 'candidates.programme_id', '=', 'programmes.id')
            ->leftJoin('countries', 'candidates.country_id', '=', 'countries.id')
            ->join('user_roles', function ($join) {
                $join->on('user_roles.user_id', '=', 'users.id')
                    ->where('user_roles.is_active', '=', 1);
            })
            ->where('users.is_deleted', '=', '0')
            ->where('candidates.exam_year', '=', strval($currentYear)); // cast to string for ENUM

        return $return->orderBy('candidates_id', 'asc')->get();

    }



    static public function getExaminerCandidates($userId = null, $yearId = null)
{
    $userId = $userId ?? Auth::id();

    // Use current year if no year specified
    if (!$yearId) {
        $yearId = self::getCurrentYearId();
    }

    // Get the actual year value (e.g., '2025') from years table
    $currentYear = DB::table('years')
        ->where('id', $yearId)
        ->value('year_name');

    // Get examiner ID first
    $examinerId = DB::table('examiners')
        ->where('user_id', $userId)
        ->value('id');

    if (!$examinerId) {
        return collect();
    }

    // Get examiner's group IDs for the specified year
    $groupIds = \DB::table('exams_groups')
        ->where('exm_id', $examinerId)
        ->where('year_id', $yearId)
        ->pluck('group_id')
        ->toArray();

    if (empty($groupIds)) {
        return collect();
    }

    // Fetch candidates with matching group_ids AND exam_year
    $candidates = self::select(
        'users.id as user_id',
        'users.name as name',
        'users.email as user_email',
        'users.user_type as user_type',
        'candidates.id as candidates_id',
        'candidates.user_id as c_id',
        'candidates.group_id as candidate_group_id',
        'candidates.exam_year',
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
        ->whereIn('candidates.group_id', $groupIds)
        ->where('candidates.exam_year', $currentYear) // Filter by exam year
        ->where('users.is_deleted', '=', '0')
        ->orderBy('candidates.id', 'asc')
        ->get();

    return $candidates;
}

    // Get candidates by specific group and year
    static public function getCandidatesByGroupAndYear($groupId, $yearId = null)
    {
        $query = self::select(
            'users.id as user_id',
            'users.name as name',
            'candidates.id as cand_id',
            'candidates.candidate_id as c_id',
            'candidates.group_id',
            'examiners_groups.group_name'
        )
            ->join('candidates', 'users.id', '=', 'candidates.user_id')
            ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
            ->where('candidates.group_id', '=', $groupId)
            ->where('users.is_deleted', '=', '0');

        return $query->orderBy('candidates.candidate_id', 'asc')->get();
    }

    // Get examiner's groups for a specific year
    static public function getExaminerGroups($userId, $yearId = null)
    {
        // Use current year if no year specified
        if (!$yearId) {
            $yearId = self::getCurrentYearId();
        }

        return \DB::table('examiners')
            ->join('exams_groups', 'examiners.id', '=', 'exams_groups.exm_id')
            ->join('examiners_groups', 'exams_groups.group_id', '=', 'examiners_groups.id')
            ->where('examiners.user_id', $userId)
            ->where('exams_groups.year_id', $yearId)
            ->select('examiners_groups.id', 'examiners_groups.group_name', 'exams_groups.year_id')
            ->get();
    }

    // Get examiner's shifts for a specific year
    static public function getExaminerShifts($userId, $yearId = null)
    {
        // Use current year if no year specified
        if (!$yearId) {
            $yearId = self::getCurrentYearId();
        }

        return \DB::table('examiners')
            ->join('exams_shifts', 'examiners.id', '=', 'exams_shifts.exm_id')
            ->where('examiners.user_id', $userId)
            ->where('exams_shifts.year_id', $yearId)
            ->select('exams_shifts.shift', 'exams_shifts.year_id')
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

        // Define the current exam year ID (you can set dynamically if needed)
        $currentExamYearId = 6; // Example for 2025

        // Fetch the most recent submission from `mcs_results` for current exam year
        $lastExamFormSubmission = \DB::table('mcs_results')
            ->where('examiner_id', $examinerId)
            ->where('exam_year', $currentExamYearId)
            ->latest('created_at')
            ->first();

        // Fetch the most recent submission from `gs_results` for current exam year
        $lastGsFormSubmission = \DB::table('gs_results')
            ->where('examiner_id', $examinerId)
            ->where('exam_year', $currentExamYearId)
            ->latest('created_at')
            ->first();

        // Determine the last submitted form
        $lastSubmittedForm = null;

        if ($lastExamFormSubmission && $lastGsFormSubmission) {
            $lastSubmittedForm = $lastExamFormSubmission->created_at > $lastGsFormSubmission->created_at
                ? 'mcs_results'
                : 'gs_results';
        } elseif ($lastExamFormSubmission) {
            $lastSubmittedForm = 'mcs_results';
        } elseif ($lastGsFormSubmission) {
            $lastSubmittedForm = 'gs_results';
        }

        // Fetch MCS results for current exam year
        $examinationFormResults = \DB::table('mcs_results')
            ->select(
                'mcs_results.*',
                'mcs_results.id as record_id',
                'mcs_results.candidate_id',
                'mcs_results.station_id',
                'mcs_results.group_id',
                'mcs_results.total as total_marks',
                'mcs_results.overall as grade',
                'mcs_results.remarks',
                'candidates.candidate_id as candidate_name',
                'examiners_groups.group_name as group_name',
                \DB::raw("'mcs_results' as source_table")
            )
            ->join('candidates', 'mcs_results.candidate_id', '=', 'candidates.id')
            ->join('examiners_groups', 'mcs_results.group_id', '=', 'examiners_groups.id')
            ->where('mcs_results.examiner_id', $examinerId)
            ->where('mcs_results.exam_year', $currentExamYearId); // ✅ filter by current exam year

        // Fetch GS results for current exam year
        $gsFormResults = \DB::table('gs_results')
            ->select(
                'gs_results.*',
                'gs_results.id as record_id',
                'gs_results.candidate_id',
                'gs_results.station_id',
                'gs_results.group_id as g_id',
                'gs_results.total as total_marks',
                \DB::raw('NULL as grade'),
                'gs_results.remarks',
                'candidates.candidate_id as candidate_name',
                'examiners_groups.group_name as group_name',
                \DB::raw("'gs_results' as source_table")
            )
            ->join('candidates', 'gs_results.candidate_id', '=', 'candidates.id')
            ->join('examiners_groups', 'gs_results.group_id', '=', 'examiners_groups.id')
            ->where('gs_results.examiner_id', $examinerId)
            ->where('gs_results.exam_year', $currentExamYearId); // ✅ filter by current exam year

        // Combine results with UNION and order by record ID
        $combinedResults = $examinationFormResults
            ->union($gsFormResults)
            ->orderBy('record_id', 'asc')
            ->get();

        return [
            'lastSubmittedForm' => $lastSubmittedForm,
            'records' => $combinedResults,
        ];
    }

//MCS Results on the admin side.
    public static function getAdminExamsResults()
    {
        $examYearId = 6; // ✅ Fixed exam year ID

        return \DB::table('mcs_results')
            ->select(
                'mcs_results.candidate_id as cand_id',
                'mcs_results.exam_year', // ✅ include exam year column
                'candidates.firstname',
                'candidates.middlename',
                'candidates.lastname',
                'candidates.candidate_id as c_id',
                'mcs_results.station_id',
                'mcs_results.total'
            )
            ->join('candidates', 'mcs_results.candidate_id', '=', 'candidates.id')
            ->where('mcs_results.exam_year', $examYearId) // ✅ filter by year ID = 6
            ->orderBy('mcs_results.candidate_id')
            ->get()
            ->groupBy('cand_id')
            ->map(function ($group) {
                $candidate = $group->first();
                return (object) [
                    'exam_year' => $candidate->exam_year, // ✅ show year ID
                    'candidate_id' => $candidate->c_id,
                    'cnd_id' => $candidate->cand_id,
                    'fullname' => trim("{$candidate->firstname} {$candidate->middlename} {$candidate->lastname}"),
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
        return \DB::table('gs_results')
            ->select(
                'gs_results.candidate_id as cand_id',
                'candidates.firstname',
                'candidates.middlename',
                'candidates.lastname',
                'candidates.candidate_id as c_id',
                'gs_results.station_id',
                \DB::raw('SUM(gs_results.total) as total_sum')
            )
            ->join('candidates', 'gs_results.candidate_id', '=', 'candidates.id')
            ->groupBy('gs_results.candidate_id', 'gs_results.station_id', 'candidates.firstname', 'candidates.middlename', 'candidates.lastname', 'candidates.candidate_id')
            ->orderBy('gs_results.candidate_id')
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
                            'total_sum' => $row->total_sum
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
            'trainers.id as trainer_id',
            'trainers.user_id as tr_id',
            'trainers.*',
            'hospitals.name as hospital_name',
            'countries.country_name as country_name'
        )
            ->join('trainers', 'users.id', '=', 'trainers.user_id')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->leftJoin('hospitals', 'trainers.hospital_id', '=', 'hospitals.id')
            ->leftJoin('countries', 'hospitals.country_id', '=', 'countries.id')
            ->where('user_roles.role_type', 4) // Ensure it's a trainer role
            ->where('user_roles.is_active', 1)
            ->orderBy('trainer_id', 'asc')
            ->get();

        return $return;
    }


    static public function getReps()
    {
        $return = self::select(
            'users.id as user_id',
            'users.name as name',
            'users.email as user_email',
            'users.password as user_password',
            'country_reps.id as reps_id',
            'country_reps.user_id as cr_id',
            'country_reps.*',
            'countries.country_name as country_name'
        )
            ->join('country_reps', 'users.id', '=', 'country_reps.user_id')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->leftJoin('countries', 'country_reps.country_id', '=', 'countries.id')
            ->where('user_roles.role_type', 5) // 5 = Country Rep
            ->where('user_roles.is_active', 1)
            ->orderBy('reps_id', 'asc')
            ->get();

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

    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function hasRole($roleType)
    {
        return $this->roles()->where('role_type', $roleType)->where('is_active', 1)->exists();
    }

    public function getRoles()
    {
        return $this->roles()->where('is_active', 1)->pluck('role_type')->toArray();
    }

    public function getActiveRole()
    {
        // Return the role from session or default to first role
        return session('active_role', $this->roles()->where('is_active', 1)->first()->role_type ?? null);
    }
}
