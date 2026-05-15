<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExaminersImport;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Country;
use App\Models\ExamsModel;
use App\Models\Attendance;
use App\Models\ExamsShift;
use App\Models\ExaminerHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ExamsController extends Controller
{
    public function list()
    {
        $currentYearId = User::getCurrentYearId();
        $lastYearId    = $currentYearId - 1;

        // Check whether the participations table has been migrated yet
        $hasParticipations = \Illuminate\Support\Facades\Schema::hasTable('examiner_participations');

        // Participation subquery fragments — include participations table only if it exists
        $participatedSql = 'EXISTS(
                    SELECT 1 FROM mcs_results WHERE mcs_results.examiner_id = examiners.id AND mcs_results.exam_year = ' . $lastYearId . '
                    UNION ALL
                    SELECT 1 FROM gs_results  WHERE gs_results.examiner_id  = examiners.id AND gs_results.exam_year  = ' . $lastYearId .
            ($hasParticipations ? '
                    UNION ALL
                    SELECT 1 FROM examiner_participations WHERE examiner_participations.exm_id = examiners.id AND examiner_participations.year_id = ' . $lastYearId : '') . '
                ) as participated_last_year';

        $examinedForSql = '(
                    SELECT GROUP_CONCAT(DISTINCT spec ORDER BY spec SEPARATOR ", ")
                    FROM (
                        SELECT "MCS" as spec FROM mcs_results
                            WHERE mcs_results.examiner_id = examiners.id AND mcs_results.exam_year = ' . $lastYearId . '
                        UNION ALL
                        SELECT "General Surgery" FROM gs_results
                            WHERE gs_results.examiner_id  = examiners.id AND gs_results.exam_year  = ' . $lastYearId .
            ($hasParticipations ? '
                        UNION ALL
                        SELECT ep.specialty FROM examiner_participations ep
                            WHERE ep.exm_id = examiners.id AND ep.year_id = ' . $lastYearId . ' AND ep.specialty IS NOT NULL' : '') . '
                    ) specs
                ) as examined_for';

        $examiners = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('countries', 'countries.id', '=', 'examiners.country_id')
            ->leftJoin('exams_groups', function ($join) use ($currentYearId) {
                $join->on('exams_groups.exm_id', '=', 'examiners.id')
                     ->where('exams_groups.year_id', $currentYearId);
            })
            ->leftJoin('examiners_groups', 'examiners_groups.id', '=', 'exams_groups.group_id')
            ->where('users.user_type', 9)
            ->select(
                'examiners.id as id',
                'examiners.id as examin_id',
                'examiners.user_id as ex_id',
                'examiners.examiner_id',
                'users.name as examiner_name',
                'users.email',
                'countries.country_name',
                DB::raw('GROUP_CONCAT(DISTINCT examiners_groups.group_name ORDER BY examiners_groups.group_name SEPARATOR ", ") as group_name'),
                DB::raw($participatedSql),
                DB::raw($examinedForSql)
            )
            ->groupBy(
                'examiners.id', 'examiners.user_id', 'examiners.examiner_id',
                'users.name', 'users.email', 'countries.country_name'
            )
            ->orderBy('users.name')
            ->get();

        // Build filter options from the already-fetched collection
        $countries  = $examiners->pluck('country_name')->filter()->unique()->sort()->values();
        $programmes = $examiners->pluck('examined_for')->filter()
            ->flatMap(fn($v) => array_map('trim', explode(',', $v)))
            ->unique()->sort()->values();

        $data['getExaminers']  = $examiners;
        $data['countries']     = $countries;
        $data['programmes']    = $programmes;
        $data['currentYear']   = date('Y');
        $data['lastYear']      = date('Y') - 1;
        $data['header_title']  = 'Examiners';

        return view('admin.exams.examiners', $data);
    }

    public function import()
    {
        $data['header_title'] = "Import Examiners";
        return view('admin.exams.import', $data);
    }

    public function importExaminers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        Excel::import(new ExaminersImport, $request->file('file'));

        return redirect('admin/exams/examiners')->with('success', 'Examiners imported successfully');
    }

    // ── Upload Confirmation ──────────────────────────────────────────────────────

    public function uploadConfirmationForm()
    {
        $years = DB::table('years')->orderBy('id', 'desc')->get();
        $data['years']        = $years;
        $data['defaultYear']  = User::getCurrentYearId() - 1; // default to last year
        $data['header_title'] = 'Upload Examiner Confirmation';
        return view('admin.exams.upload_confirmation', $data);
    }

    public function processConfirmationUpload(Request $request)
    {
        $request->validate([
            'file'    => 'required|mimes:xlsx,xls,csv|max:5120',
            'year_id' => 'required|integer',
        ]);

        $yearId = (int) $request->year_id;

        // Read raw rows (skip header row 0)
        $rows = Excel::toArray([], $request->file('file'));
        $sheet = $rows[0] ?? [];

        $results = [
            'updated'        => [],
            'duplicate_file' => [],
            'already_exists' => [],
            'not_found'      => [],
            'error'          => [],
        ];

        $seenKeys   = []; // email|specialty → detect in-file duplicates
        $yearName   = DB::table('years')->where('id', $yearId)->value('year_name');

        foreach ($sheet as $i => $row) {
            if ($i === 0) continue; // skip header

            $name        = trim($row[0] ?? '');
            $email       = strtolower(trim($row[1] ?? ''));
            $specialty   = trim($row[2] ?? '');
            $country     = trim($row[3] ?? '');
            $role        = trim($row[4] ?? '');
            $fellowship  = trim($row[5] ?? '');
            $subSpec     = trim($row[6] ?? '');

            // Skip blank rows
            if ($name === '' && $email === '') continue;

            // Validate required fields
            if ($email === '') {
                $results['error'][] = [
                    'name' => $name, 'email' => '—', 'specialty' => $specialty,
                    'reason' => 'Missing email address',
                ];
                continue;
            }
            if ($specialty === '') {
                $results['error'][] = [
                    'name' => $name, 'email' => $email, 'specialty' => '—',
                    'reason' => 'Missing specialty',
                ];
                continue;
            }

            // Detect duplicates within the file
            $key = $email . '|' . strtolower($specialty);
            if (in_array($key, $seenKeys)) {
                $results['duplicate_file'][] = [
                    'name' => $name, 'email' => $email, 'specialty' => $specialty,
                    'reason' => 'Same examiner + specialty appears more than once in the file',
                ];
                continue;
            }
            $seenKeys[] = $key;

            // ── Match examiner in the system ──────────────────────────────────
            $user = DB::table('users')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            // Fallback: match by first + last name (case-insensitive)
            if (!$user && $name !== '') {
                $parts     = preg_split('/\s+/', $name);
                $firstName = strtolower($parts[0] ?? '');
                $lastName  = strtolower(end($parts));

                if ($firstName && $lastName && $firstName !== $lastName) {
                    $user = DB::table('users')
                        ->where('user_type', 9)
                        ->whereRaw('LOWER(name) LIKE ?', ["%{$firstName}%"])
                        ->whereRaw('LOWER(name) LIKE ?', ["%{$lastName}%"])
                        ->first();
                }
            }

            if (!$user) {
                $results['not_found'][] = [
                    'name' => $name, 'email' => $email, 'specialty' => $specialty,
                    'reason' => 'No matching user found in the system',
                ];
                continue;
            }

            $examiner = DB::table('examiners')->where('user_id', $user->id)->first();
            if (!$examiner) {
                $results['not_found'][] = [
                    'name' => $name, 'email' => $email, 'specialty' => $specialty,
                    'matched_name' => $user->name,
                    'reason' => 'User found but has no examiner record',
                ];
                continue;
            }

            // Check if already recorded for this year + specialty
            $existing = DB::table('examiner_participations')
                ->where('exm_id', $examiner->id)
                ->where('year_id', $yearId)
                ->where('specialty', $specialty)
                ->exists();

            if ($existing) {
                $results['already_exists'][] = [
                    'name' => $name, 'email' => $email, 'specialty' => $specialty,
                    'matched_name' => $user->name,
                    'reason' => 'Already recorded for this year & specialty',
                ];
                continue;
            }

            // Insert participation record
            DB::table('examiner_participations')->insert([
                'exm_id'       => $examiner->id,
                'year_id'      => $yearId,
                'specialty'    => $specialty,
                'role'         => $role ?: null,
                'sub_specialty'=> $subSpec ?: null,
                'fellowship_no'=> $fellowship ?: null,
                'source'       => 'upload',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $results['updated'][] = [
                'name' => $name, 'email' => $email, 'specialty' => $specialty,
                'matched_name' => $user->name,
            ];
        }

        return view('admin.exams.upload_confirmation_result', compact('results', 'yearName'));
    }

    public function add()
    {
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Examiner";
        $data['groups'] = DB::table('examiners_groups')->select('id', 'group_name')->get();
        return view('admin.exams.add_examiner', $data);
    }

    public function insert(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'user_type' => 9,
            ]);

            // Link to roles table
            DB::table('user_roles')->insert([
                'user_id' => $user->id,
                'role_type' => 9,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $examiner = ExamsModel::create([
                'user_id' => $user->id,
                'examiner_id' => $request->examiner_id,
                'country_id' => $request->country_id,
                'mobile' => $request->mobile,
                'specialty' => $request->specialty,
                'subspecialty' => $request->subspecialty,
                'gender' => $request->gender,
                'role_id' => $request->participation_type === 'Examiner' ? 1 : ($request->participation_type === 'Observer' ? 2 : 3),
            ]);

            // attach group
            ExamsShift::create([
                'exm_id' => $examiner->id,
                'shift' => $request->shift,
                'year_id' => User::getCurrentYearId()
            ]);

            DB::table('exams_groups')->insert([
                'exm_id' => $examiner->id,
                'group_id' => $request->group_id,
                'year_id' => User::getCurrentYearId(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // New logic: prioritize "Not Available"
            $availability = $request->exam_availability ?? [];
            if (in_array('Not Available', $availability)) {
                $availability = ['Not Available'];
            }

            ExaminerHistory::create([
                'exm_id' => $examiner->id,
                'virtual_mcs_participated' => $request->virtual_mcs_participated ?? null,
                'fcs_participated' => $request->fcs_participated ?? null,
                'participation_type' => $request->participation_type,
                'hospital_type' => $request->hospital_type ?? null,
                'hospital_name' => $request->hospital_name ?? null,
                'exam_availability' => json_encode($availability),
                'examination_years' => isset($request->examination_years) ? json_encode($request->examination_years) : null,
            ]);

            DB::commit();
            return redirect('admin/exams/examiners')->with('success', 'Examiner added successfully');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->with('error', 'Insert failed: ' . $e->getMessage());
        }
    }

    // public function edit($id, Request $request)
    // {
    //     $examiner = User::getExaminers()->firstWhere('examin_id', $id);
    //     if (!$examiner) return redirect()->back()->with('error', 'Examiner not found');

    //     // Get the `from` URL (fallback to examiners list if missing)
    //     $from = $request->input('from', 'admin/exams/examiners');
    //     $query = $request->except(['from', '_token']);
    //     $backUrl = url($from) . (count($query) ? '?' . http_build_query($query) : '');

    //     return view('admin.exams.edit_examiner', [
    //         'header_title' => 'Edit Examiner',
    //         'examiner' => $examiner,
    //         'getCountry' => Country::getCountry(),
    //         'groups' => DB::table('examiners_groups')->select('id', 'group_name')->get(),
    //         'backUrl' => $backUrl,
    //     ]);
    // }


    // public function update(Request $request, $id)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $examiner = ExamsModel::findOrFail($id);
    //         $user = User::findOrFail($examiner->user_id);

    //         // Update basic user info
    //         $user->update([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'password' => $request->password ? bcrypt($request->password) : $user->password
    //         ]);

    //         // Update examiner info
    //         $examiner->update([
    //             'gender' => $request->gender,
    //             'examiner_id' => $request->examiner_id,
    //             'country_id' => $request->country_id,
    //             'mobile' => $request->mobile,
    //             'specialty' => $request->specialty,
    //             'subspecialty' => $request->subspecialty,
    //             'role_id' => $request->participation_type === 'Examiner' ? 1 : ($request->participation_type === 'Observer' ? 2 : 3)
    //         ]);

    //         // Update user_roles
    //         DB::table('user_roles')
    //             ->where('user_id', $user->id)
    //             ->where('role_type', 9)
    //             ->update([
    //                 'updated_at' => now(),
    //                 'is_active' => 1,
    //             ]);

    //         // Update group
    //         DB::table('exams_groups')->where('exm_id', $examiner->id)->delete();
    //         DB::table('exams_groups')->insert([
    //             'exm_id' => $examiner->id,
    //             'group_id' => $request->group_id,
    //             'year_id' => User::getCurrentYearId(),
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         // Update shift
    //         ExamsShift::where('exm_id', $examiner->id)
    //             ->where('year_id', User::getCurrentYearId())->delete();

    //         ExamsShift::create([
    //             'exm_id' => $examiner->id,
    //             'shift' => $request->shift,
    //             'year_id' => User::getCurrentYearId()
    //         ]);

    //         // Handle Not Available logic
    //         $availability = $request->exam_availability ?? [];
    //         if (in_array('Not Available', $availability)) {
    //             $availability = ['Not Available'];
    //         }

    //         ExaminerHistory::updateOrCreate(
    //             ['exm_id' => $examiner->id],
    //             [
    //                 'virtual_mcs_participated' => $request->virtual_mcs_participated ?? null,
    //                 'fcs_participated' => $request->fcs_participated ?? null,
    //                 'participation_type' => $request->participation_type,
    //                 'hospital_type' => $request->hospital_type ?? null,
    //                 'hospital_name' => $request->hospital_name ?? null,
    //                 'exam_availability' => json_encode($availability),
    //                 'examination_years' => isset($request->examination_years) ? json_encode($request->examination_years) : null,
    //             ]
    //         );

    //         DB::commit();

    //         // ✅ Redirect back to the original page
    //         $redirectUrl = $request->input('back_url') ?: 'admin/exams/examiners';
    //         return redirect($redirectUrl)->with('success', 'Examiner updated successfully');

    //     } catch (\Throwable $e) {
    //         DB::rollback();
    //         return back()->with('error', 'Update failed: ' . $e->getMessage());
    //     }
    // }


    public function edit($id, Request $request)
    {
        $examiner = User::getExaminers()->firstWhere('examin_id', $id);
        if (!$examiner) return redirect()->back()->with('error', 'Examiner not found');

        // Get the referring URL or fallback to examiners list
        $from = $request->input('from');
        $backUrl = null;

        if ($from) {
            // If 'from' parameter exists, use it
            $query = $request->except(['from', '_token']);
            $backUrl = url($from) . (count($query) ? '?' . http_build_query($query) : '');
        } else {
            // If no 'from' parameter, try to get from HTTP_REFERER
            $referer = $request->header('referer');
            if ($referer && str_contains($referer, url('/'))) {
                $backUrl = $referer;
            } else {
                // Default fallback
                $backUrl = url('admin/exams/examiners');
            }
        }

        return view('admin.exams.edit_examiner', [
            'header_title' => 'Edit Examiner',
            'examiner' => $examiner,
            'getCountry' => Country::getCountry(),
            'groups' => DB::table('examiners_groups')->select('id', 'group_name')->get(),
            'backUrl' => $backUrl,
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $examiner = ExamsModel::findOrFail($id);
            $user = User::findOrFail($examiner->user_id);

            // Update basic user info
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? bcrypt($request->password) : $user->password
            ]);

            // Update examiner info
            $examiner->update([
                'gender' => $request->gender,
                'examiner_id' => $request->examiner_id,
                'country_id' => $request->country_id,
                'mobile' => $request->mobile,
                'specialty' => $request->specialty,
                'subspecialty' => $request->subspecialty,
                'role_id' => $request->participation_type === 'Examiner' ? 1 : ($request->participation_type === 'Observer' ? 2 : 3)
            ]);

            // Update user_roles
            DB::table('user_roles')
                ->where('user_id', $user->id)
                ->where('role_type', 9)
                ->update([
                    'updated_at' => now(),
                    'is_active' => 1,
                ]);

            // Update group
            DB::table('exams_groups')->where('exm_id', $examiner->id)->delete();
            DB::table('exams_groups')->insert([
                'exm_id' => $examiner->id,
                'group_id' => $request->group_id,
                'year_id' => User::getCurrentYearId(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update shift
            ExamsShift::where('exm_id', $examiner->id)
                ->where('year_id', User::getCurrentYearId())->delete();

            ExamsShift::create([
                'exm_id' => $examiner->id,
                'shift' => $request->filled('shift') ? $request->shift : null,
                'year_id' => User::getCurrentYearId()
            ]);

            // Handle Not Available logic
            $availability = $request->exam_availability ?? [];
            if (in_array('Not Available', $availability)) {
                $availability = ['Not Available'];
            }

            ExaminerHistory::updateOrCreate(
                ['exm_id' => $examiner->id],
                [
                    'virtual_mcs_participated' => $request->virtual_mcs_participated ?? null,
                    'fcs_participated' => $request->fcs_participated ?? null,
                    'participation_type' => $request->participation_type,
                    'hospital_type' => $request->hospital_type ?? null,
                    'hospital_name' => $request->hospital_name ?? null,
                    'exam_availability' => json_encode($availability),
                    'examination_years' => isset($request->examination_years) ? json_encode($request->examination_years) : null,
                ]
            );

            DB::commit();

            // Get the back URL from the form submission
            $backUrl = $request->input('back_url');

            // Validate the back URL to ensure it's from your domain
            if ($backUrl && str_contains($backUrl, url('/'))) {
                return redirect($backUrl)->with('success', 'Examiner updated successfully');
            } else {
                // Fallback to default page if back_url is invalid or missing
                return redirect('admin/exams/examiners')->with('success', 'Examiner updated successfully');
            }
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function view($id, Request $request)
    {
        $yearId = User::getCurrentYearId();

        // Fetch ONLY the requested examiner — no full collection load
        $examiner = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('countries', 'countries.id', '=', 'examiners.country_id')
            ->where('examiners.id', $id)
            ->select(
                'examiners.id as examin_id',
                'examiners.user_id as ex_id',
                'examiners.examiner_id',
                'examiners.mobile',
                'examiners.gender',
                'examiners.country_id',
                'examiners.specialty',
                'examiners.subspecialty',
                'examiners.curriculum_vitae',
                'examiners.passport_image',
                'examiners.role_id',
                'users.name as examiner_name',
                'users.email',
                'countries.country_name'
            )
            ->first();

        if (!$examiner) return redirect()->back()->with('error', 'Examiner not found');

        // Groups for current year (tiny result set)
        $groups = DB::table('exams_groups')
            ->join('examiners_groups', 'examiners_groups.id', '=', 'exams_groups.group_id')
            ->where('exams_groups.exm_id', $id)
            ->where('exams_groups.year_id', $yearId)
            ->select('examiners_groups.id as group_id', 'examiners_groups.group_name', 'exams_groups.year_id')
            ->get();

        $examiner->groups     = $groups;
        $examiner->group_name = $groups->isNotEmpty() ? $groups->pluck('group_name')->implode(', ') : null;
        $examiner->group_id   = $groups->isNotEmpty() ? $groups->first()->group_id : null;

        // Shifts for current year
        $shifts = DB::table('exams_shifts')
            ->where('exm_id', $id)
            ->where('year_id', $yearId)
            ->select('shift', 'year_id')
            ->get();

        $examiner->shifts   = $shifts;
        $examiner->shift_id = $shifts->isNotEmpty() ? $shifts->first()->shift : null;
        $examiner->shift    = $shifts->isNotEmpty() ? User::getShiftName($shifts->first()->shift) : null;

        // History record
        $history = DB::table('examiners_history')->where('exm_id', $id)->first();
        $examiner->history = $history;
        if ($history) {
            $examiner->virtual_mcs_participated = $history->virtual_mcs_participated;
            $examiner->fcs_participated         = $history->fcs_participated;
            $examiner->hospital_type            = $history->hospital_type;
            $examiner->hospital_name            = $history->hospital_name;
            $examiner->examination_years        = $history->examination_years;
        }

        $from    = $request->input('from', 'admin/exams/examiners');
        $query   = $request->except(['from', '_token']);
        $backUrl = url($from) . (count($query) ? '?' . http_build_query($query) : '');

        $qrCode = QrCode::size(70)
            ->generate(url("/admin/exams/confirm-attendance/{$examiner->examin_id}"));

        return view('admin.exams.view_examiner', [
            'header_title' => 'View Examiner',
            'examiner'     => $examiner,
            'getCountry'   => Country::getCountry(),
            'groups'       => DB::table('examiners_groups')->select('id', 'group_name')->get(),
            'qrCode'       => $qrCode,
            'backUrl'      => $backUrl,
        ]);
    }
public function delete($id)
{
    $user = User::find($id);

    if (!$user) {
        return redirect('admin/exams/examiners')->with('error', 'User not found');
    }

    $examiner = ExamsModel::where('user_id', $user->id)->first();

    if (!$examiner) {
        return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
    }

    if ($user->user_type != 9) {
        return redirect('admin/exams/examiners')->with('error', 'User is not an Examiner');
    }

    $updated = DB::table('user_roles')
        ->where('user_id', $user->id)
        ->where('role_type', 9)
        ->update(['is_active' => 0, 'updated_at' => now()]);

    if ($updated) {
        return redirect('admin/exams/examiners')->with('success', 'Examiner successfully deactivated');
    }

    return redirect('admin/exams/examiners')->with('error', 'Failed to deactivate examiner');
}


    /**
     * Generate visual report for examiner confirmations
     */
    public function generateVisualReport()
    {
        $getExaminers = $this->getExaminersData();

        // Process availability data
        $availabilityData = $this->processAvailabilityData($getExaminers);

        // Process participation data
        $participationData = $this->processParticipationData($getExaminers);

        // Process country data
        $countryData = $this->processCountryData($getExaminers);

        $data = [
            'availabilityData' => $availabilityData,
            'participationData' => $participationData,
            'countryData' => $countryData,
            'header_title' => 'Examiner Confirmation Visual Report'
        ];

        return view('admin.exams.visual_report', $data);
    }

    private function getExaminersData()
    {
        $yearId = User::getCurrentYearId();

        return DB::table('examiners')
            ->leftJoin('examiners_history', 'examiners.id', '=', 'examiners_history.exm_id')
            ->leftJoin('users', 'examiners.user_id', '=', 'users.id')
            ->leftJoin('countries', 'examiners.country_id', '=', 'countries.id')
            ->leftJoin('exams_groups', function ($join) use ($yearId) {
                $join->on('examiners.id', '=', 'exams_groups.exm_id')
                     ->where('exams_groups.year_id', '=', $yearId);
            })
            ->leftJoin('examiners_groups', 'exams_groups.group_id', '=', 'examiners_groups.id')
            ->leftJoin('user_roles', 'examiners.user_id', '=', 'user_roles.user_id')
            ->leftJoin('exams_shifts', function ($join) use ($yearId) {
                $join->on('examiners.id', '=', 'exams_shifts.exm_id')
                     ->where('exams_shifts.year_id', '=', $yearId);
            })
            ->select(
                'examiners.id',
                DB::raw('MAX(examiners.examiner_id) as examiner_id'),
                DB::raw('MAX(examiners.mobile) as mobile'),
                DB::raw('MAX(examiners.gender) as gender'),
                DB::raw('MAX(examiners.country_id) as country_id'),
                DB::raw('MAX(examiners.user_id) as user_id'),
                DB::raw('MAX(examiners.specialty) as specialty'),
                DB::raw('MAX(examiners.subspecialty) as subspecialty'),
                DB::raw('MAX(examiners.curriculum_vitae) as curriculum_vitae'),
                DB::raw('MAX(examiners.passport_image) as passport_image'),
                DB::raw('MAX(examiners.role_id) as role_id'),
                DB::raw("CASE
                WHEN MAX(examiners.role_id) = 1 THEN 'Examiner'
                WHEN MAX(examiners.role_id) = 2 THEN 'Observer'
                WHEN MAX(examiners.role_id) = 3 THEN 'None'
                ELSE 'Unknown'
            END as participation_type"),
                DB::raw('MAX(users.name) as examiner_name'),
                DB::raw('MAX(users.email) as email'),
                DB::raw('MAX(examiners_history.exam_availability) as exam_availability'),
                DB::raw('MAX(examiners_history.virtual_mcs_participated) as virtual_mcs_participated'),
                DB::raw('MAX(examiners_history.fcs_participated) as fcs_participated'),
                DB::raw('MAX(examiners_history.hospital_type) as hospital_type'),
                DB::raw('MAX(examiners_history.hospital_name) as hospital_name'),
                DB::raw('MAX(countries.country_name) as country_name'),
                DB::raw('GROUP_CONCAT(DISTINCT examiners_groups.group_name ORDER BY examiners_groups.group_name SEPARATOR ", ") as group_names'),
                DB::raw('MAX(exams_shifts.shift) as shift'),
                DB::raw('MAX(examiners_history.created_at) as history_created_at'),
                DB::raw('MAX(examiners_history.updated_at) as history_updated_at'),
                DB::raw('TIMESTAMPDIFF(SECOND, MAX(examiners_history.created_at), MAX(examiners_history.updated_at)) as history_time_diff_seconds'),
                DB::raw('TIMESTAMPDIFF(MINUTE, MAX(examiners_history.created_at), MAX(examiners_history.updated_at)) as history_time_diff_minutes')
            )
            ->where(function ($query) {
                $query->where('user_roles.role_type', 9)
                    ->orWhereNull('user_roles.role_type');
            })
            ->where(function ($query) {
                // Only show current-year confirmations:
                // either they submitted the availability form (exams_shifts scoped to yearId above)
                // or the admin assigned them to a current-year exam group.
                $query->whereNotNull('exams_shifts.id')
                      ->orWhereNotNull('exams_groups.id');
            })
            ->groupBy('examiners.id')
            ->orderBy('examiners.id', 'desc')
            ->get();
    }

    private function processAvailabilityData($examiners)
    {
        $availabilityCount = [
            'FCS' => 0,
            'MCS' => 0,
            'FCS and MCS' => 0,
            'Not Available' => 0
        ];

        foreach ($examiners as $examiner) {
            $availability = [];

            if (!empty($examiner->exam_availability)) {
                $decoded = json_decode($examiner->exam_availability, true);

                if (is_string($decoded)) {
                    $availability = json_decode($decoded, true) ?: [];
                } elseif (is_array($decoded)) {
                    $availability = $decoded;
                } else {
                    $cleaned = str_replace('\\"', '"', $examiner->exam_availability);
                    $availability = json_decode($cleaned, true) ?: [];
                }
            }

            if (in_array('Not Available', $availability)) {
                $availabilityCount['Not Available']++;
            } elseif (in_array('FCS', $availability) && in_array('MCS', $availability)) {
                $availabilityCount['FCS and MCS']++;
            } elseif (in_array('FCS', $availability)) {
                $availabilityCount['FCS']++;
            } elseif (in_array('MCS', $availability)) {
                $availabilityCount['MCS']++;
            }
        }

        return $availabilityCount;
    }

    private function processParticipationData($examiners)
    {
        $participationCount = [];

        foreach ($examiners as $examiner) {
            $type = $examiner->participation_type ?? 'Unknown';
            $participationCount[$type] = ($participationCount[$type] ?? 0) + 1;
        }

        return $participationCount;
    }

    private function processCountryData($examiners)
    {
        $countryCount = [];

        foreach ($examiners as $examiner) {
            $country = $examiner->country_name ?? 'Unknown';
            $countryCount[$country] = ($countryCount[$country] ?? 0) + 1;
        }

        // Sort by count descending and take top 10
        arsort($countryCount);
        return array_slice($countryCount, 0, 10, true);
    }

    // Update your existing ExaminerconfirmationView method
    public function ExaminerconfirmationView()
    {
        $getExaminers = $this->getExaminersData();

        $data['getExaminers'] = $getExaminers;
        $data['header_title'] = 'Examiner Confirmation';

        return view('admin.exams.examiner_confirmation', $data);
    }

    /**
     * Show attendance confirmation page after QR scan
     */
    public function showAttendanceConfirmation($examiner_id)
    {
        $examiner = User::getExaminers()->firstWhere('examin_id', $examiner_id);

        if (!$examiner) {
            return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
        }

        // Check if already registered today
        $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        $data = [
            'header_title' => 'Confirm Attendance Registration',
            'examiner' => $examiner,
            'already_registered' => $existingAttendance ? true : false,
            'registration_time' => $existingAttendance ? $existingAttendance->created_at->format('H:i:s') : null
        ];

        return view('admin.exams.confirm_attendance', $data);
    }

    /**
     * Process attendance registration after confirmation
     */
    public function confirmAttendanceRegistration(Request $request, $examiner_id)
    {
        try {
            $examiner = User::getExaminers()->firstWhere('examin_id', $examiner_id);

            if (!$examiner) {
                return redirect()->back()->with('error', 'Examiner not found');
            }

            // Check for duplicate attendance (same examiner same day)
            $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existingAttendance) {
                return redirect()->back()->with(
                    'info',
                    'Attendance already recorded for today at ' . $existingAttendance->created_at->format('H:i:s')
                );
            }

            // Create new attendance record
            $attendance = Attendance::create([
                'user_id'        => $examiner->user_id ?? null,
                'examiner_id'    => $examiner->examin_id ?? null,
                'country_id'     => $examiner->country_id ?? null,
                'group_id'       => $examiner->group_id ?? null,
                'mobile'         => $examiner->mobile ?? null,
                'specialty'      => $examiner->specialty ?? null,
                'subspecialty'   => $examiner->subspecialty ?? null,
                'shift'          => $examiner->shift ?? null,
                'curriculum_vitae' => $examiner->curriculum_vitae ?? null,
                'passport_image' => $examiner->passport_image ?? null,
            ]);

            return redirect()->back()->with(
                'success',
                'Attendance registered successfully for ' . $examiner->examiner_name . ' at ' . $attendance->created_at->format('H:i:s')
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error registering attendance: ' . $e->getMessage());
        }
    }


    public function adminResults()
    {
        $data['header_title'] = 'MCS Results';
        $getResults = User::getAdminExamsResults();
        $data['getResults'] = $getResults;
        return view('admin.exams.exam_results', $data);
    }

    public function gsResults()
    {
        $data['header_title'] = 'GS Results';
        $getResults = User::getGsResults();
        $data['getResults'] = $getResults;
        return view('admin.exams.gs_results', $data);
    }

    // Add this helper method to get FCS results
    // Better version - properly aggregates multiple examiner results per station
    private function getFcsResults($programmeId, $tableName)
    {
        $examYearId = User::getCurrentYearId();

        // First, get all raw results
        $rawResults = DB::table($tableName)
            ->join('candidates', "$tableName.candidate_id", '=', 'candidates.id')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
            ->select(
                'candidates.id as cnd_id',
                'candidates.candidate_id',
                'users.name as fullname',
                'examiners_groups.group_name',
                "$tableName.exam_format",
                "$tableName.station_id",
                "$tableName.total"
            )
            ->where('candidates.programme_id', $programmeId)
            ->where("$tableName.exam_year", $examYearId)
            ->orderBy('candidates.candidate_id')
            ->get();

        // Group and aggregate by candidate
        return $rawResults
            ->groupBy('cnd_id')
            ->map(function ($candidateRecords) {
                // Separate clinical and viva
                $clinical = $candidateRecords->where('exam_format', 'clinical');
                $viva = $candidateRecords->where('exam_format', 'viva');

                // Aggregate clinical stations (sum all examiners per station)
                $clinicalStations = [];
                foreach ($clinical as $record) {
                    $stationId = $record->station_id;
                    if (!isset($clinicalStations[$stationId])) {
                        $clinicalStations[$stationId] = 0;
                    }
                    $clinicalStations[$stationId] += $record->total;
                }

                // Aggregate viva stations (sum all examiners per station)
                $vivaStations = [];
                foreach ($viva as $record) {
                    $stationId = $record->station_id;
                    if (!isset($vivaStations[$stationId])) {
                        $vivaStations[$stationId] = 0;
                    }
                    $vivaStations[$stationId] += $record->total;
                }

                return (object)[
                    'cnd_id' => $candidateRecords->first()->cnd_id,
                    'candidate_id' => $candidateRecords->first()->candidate_id,
                    'fullname' => $candidateRecords->first()->fullname,
                    'group_name' => $candidateRecords->first()->group_name,
                    'clinical_total' => array_sum($clinicalStations),
                    'viva_total' => array_sum($vivaStations),
                    'overall_total' => array_sum($clinicalStations) + array_sum($vivaStations),
                    'clinical_stations' => $clinicalStations,
                    'viva_stations' => $vivaStations,
                ];
            })
            ->values();
    }

// Cardiothoracic Results
    public function cardiothoracicResults()
    {
        $data['header_title'] = 'FCS Cardiothoracic Results';
        $data['getResults'] = $this->getFcsResults(1, 'cardiothoracic_results');
        $data['programmeName'] = 'Cardiothoracic Surgery';
        return view('admin.exams.fcs_cardiothoracic_results', $data);
    }

// Urology Results
    public function urologyResults()
    {
        $data['header_title'] = 'FCS Urology Results';
        $data['getResults'] = $this->getFcsResults(9, 'urology_results');
        $data['programmeName'] = 'Urology';
        return view('admin.exams.fcs_urology_results', $data);
    }

// Paediatric Surgery Results
    public function paediatricResults()
    {
        $data['header_title'] = 'FCS Paediatric Surgery Results';
        $data['getResults'] = $this->getFcsResults(7, 'paediatric_results');
        $data['programmeName'] = 'Paediatric Surgery';
        return view('admin.exams.fcs_paediatric_results', $data);
    }

// ENT Results
    public function entResults()
    {
        $data['header_title'] = 'FCS ENT Results';
        $data['getResults'] = $this->getFcsResults(5, 'ent_results');
        $data['programmeName'] = 'ENT Surgery';
        return view('admin.exams.fcs_ent_results', $data);
    }

// Plastic Surgery Results
    public function plasticSurgeryResults()
    {
        $data['header_title'] = 'FCS Plastic Surgery Results';
        $data['getResults'] = $this->getFcsResults(8, 'plastic_surgery_results');
        $data['programmeName'] = 'Plastic Surgery';
        return view('admin.exams.fcs_plastic_surgery_results', $data);
    }

// Neurosurgery Results
    public function neurosurgeryResults()
    {
        $data['header_title'] = 'FCS Neurosurgery Results';
        $data['getResults'] = $this->getFcsResults(3, 'neurosurgery_results');
        $data['programmeName'] = 'Neurosurgery';
        return view('admin.exams.fcs_neurosurgery_results', $data);
    }

// Orthopaedics Results
    public function orthopaedicsResults()
    {
        $data['header_title'] = 'FCS Orthopaedics Results';
        $data['getResults'] = $this->getFcsResults(4, 'orthopaedic_results');
        $data['programmeName'] = 'Orthopaedics';
        return view('admin.exams.fcs_orthopaedics_results', $data);
    }

// Paediatric Orthopaedics Results
    public function paediatricOrthopaedicsResults()
    {
        $data['header_title'] = 'FCS Paediatric Orthopaedics Results';
        $data['getResults'] = $this->getFcsResults(6, 'paediatric_orthopaedics_results');
        $data['programmeName'] = 'Paediatric Orthopaedics';
        return view('admin.exams.fcs_paediatric_ortho_results', $data);
    }

// View detailed station results for FCS programmes
// View detailed station results for FCS programmes (supports multiple examiners)
    public function viewFcsStationResults($candidate_id, $station_id, $exam_format, $table)
    {
        $header_title = ucfirst($exam_format) . ' Station Results';

        // Get the primary candidate result (first examiner)
        $candidateResult = DB::table($table)
            ->join('candidates', "$table.candidate_id", '=', 'candidates.id')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->join('examiners_groups', "$table.group_id", '=', 'examiners_groups.id')
            ->join('examiners', "$table.examiner_id", '=', 'examiners.id')
            ->join('users as examiner_users', 'examiners.user_id', '=', 'examiner_users.id')
            ->select(
                'candidates.candidate_id as candidate_name',
                'users.name as fullname',
                'examiners_groups.group_name as g_name',
                "$table.station_id as s_id",
                "$table.total",
                "$table.question_mark",
                "$table.remarks",
                "$table.exam_format",
                'examiners.examiner_id as examin_id',
                'examiner_users.name as examiner_name'
            )
            ->where("$table.candidate_id", $candidate_id)
            ->where("$table.station_id", $station_id)
            ->where("$table.exam_format", $exam_format)
            ->first();

        // Get ALL results for this station from all examiners
        $allResults = DB::table($table)
            ->join('examiners', "$table.examiner_id", '=', 'examiners.id')
            ->join('examiners_groups', "$table.group_id", '=', 'examiners_groups.id')
            ->join('users as examiner_users', 'examiners.user_id', '=', 'examiner_users.id')
            ->select(
                "$table.total",
                "$table.question_mark",
                "$table.station_id as s_id",
                "$table.exam_format",
                'examiners_groups.group_name as g_name',
                "$table.remarks",
                'examiners.examiner_id',
                'examiner_users.name as examiner_name'
            )
            ->where("$table.candidate_id", $candidate_id)
            ->where("$table.station_id", $station_id)
            ->where("$table.exam_format", $exam_format)
            ->get();

        return view('admin.exams.fcs_station_results', compact('candidateResult', 'allResults', 'header_title'));
    }

    // Single Station Results
    public function viewCandidateStationResult($candidate_id, $station_id)
    {
        $header_title = 'Station Results';
        $candidateResult = \DB::table('mcs_results')
            ->join('candidates', 'mcs_results.candidate_id', '=', 'candidates.id')
            ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
            ->join('examiners', 'examiners.id', '=', 'mcs_results.examiner_id')
            ->join('users', 'examiners.user_id', '=', 'users.id')
            ->select(
                'candidates.candidate_id as candidate_name',
                'examiners_groups.group_name',
                'mcs_results.station_id',
                'mcs_results.total',
                'mcs_results.question_mark',
                'mcs_results.overall',
                'mcs_results.remarks',
                'examiners.examiner_id as examin_id',
                'users.name as examiner_name'
            )
            ->where('mcs_results.candidate_id', $candidate_id)
            ->where('mcs_results.station_id', $station_id)
            ->first();

        return view('admin.exams.station_results', compact('candidateResult', 'header_title'));
    }

    public function viewGsStationResult($candidate_id, $station_id)
    {
        $header_title = 'Station Results';

        // Fetch the primary candidate result
        $candidateResult = \DB::table('gs_results')
            ->join('candidates', 'gs_results.candidate_id', '=', 'candidates.id')
            ->join('examiners_groups', 'gs_results.group_id', '=', 'examiners_groups.id')
            ->join('examiners', 'gs_results.examiner_id', '=', 'examiners.id')
            ->join('users', 'examiners.user_id', '=', 'users.id')
            ->select(
                'candidates.candidate_id as candidate_name',
                'examiners_groups.group_name as g_name',
                'gs_results.station_id as s_id',
                'gs_results.total',
                'gs_results.question_mark',
                'gs_results.remarks',
                'examiners.examiner_id as examin_id',
                'users.name as examiner_name'
            )
            ->where('gs_results.candidate_id', $candidate_id)
            ->where('gs_results.station_id', $station_id)
            ->first();

        // Fetch all results for the station by all examiners
        $allResults = \DB::table('gs_results')
            ->join('examiners', 'gs_results.examiner_id', '=', 'examiners.id')
            ->join('examiners_groups', 'gs_results.group_id', '=', 'examiners_groups.id')
            ->join('users', 'examiners.user_id', '=', 'users.id')
            ->select(
                'gs_results.total',
                'gs_results.question_mark',
                'gs_results.station_id as s_id',
                'examiners_groups.group_name as g_name',
                'gs_results.remarks',
                'examiners.examiner_id',
                'users.name as examiner_name'
            )
            ->where('gs_results.candidate_id', $candidate_id)
            ->where('gs_results.station_id', $station_id)
            ->get();

        return view('admin.exams.gs_station_results', compact('candidateResult', 'allResults', 'header_title'));
    }

    // Function to change password
    public function changePassword()
    {
        $data['header_title'] = "Change Password";
        return view('examiner.change_password', $data);
    }

    public function updatePassword(Request $request)
    {
        $user = User::getSingleId(Auth::user()->id);
        if (Hash::check($request->old_password, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();
            return redirect()->back()->with('success', "Password successfully updated");
        } else {
            return redirect()->back()->with('error', "Old Password is not correct");
        }
    }

    public function examinerProfile()
    {
        $user = Auth::user();

        // Get examiner details using the same method as admin
        $examiner = User::getExaminers()->firstWhere('user_id', $user->id);

        if (!$examiner) {
            return redirect('examiner/dashboard')->with('error', 'Examiner profile not found');
        }

        // Generate QR code for the examiner using examiner-specific route
        $baseUrl = request()->getSchemeAndHttpHost();
        // $baseUrl = 'http://localhost/cosecsa';

        $confirmationUrl = $baseUrl . '/examiner/confirm-attendance/' . $examiner->examin_id;

        // Generate QR code with the confirmation URL
        $qrCode = \QrCode::size(70)->generate($confirmationUrl);

        $data = [
            'header_title' => 'Profile Settings',
            'examiner' => $examiner,
            'getCountry' => Country::getCountry(),
            'groups' => DB::table('examiners_groups')->select('id', 'group_name')->get(),
            'qrCode' => $qrCode // Add QR code to the data
        ];

        return view('examiner.profile_settings', $data);
    }

    // Add this new method for generating examiner badge
    public function examinerBadge()
    {
        $user = Auth::user();

        // Get examiner details
        $examiner = User::getExaminers()->firstWhere('user_id', $user->id);

        if (!$examiner) {
            return redirect('examiner/profile_settings')->with('error', 'Examiner profile not found');
        }

        // Generate QR code using examiner-specific route
        $baseUrl = request()->getSchemeAndHttpHost();
        $confirmationUrl = $baseUrl . '/examiner/confirm-attendance/' . $examiner->examin_id;
        $qrCode = \QrCode::size(70)->generate($confirmationUrl);

        $data = [
            'header_title' => 'ID Badge',
            'examiner' => $examiner,
            'qrCode' => $qrCode
        ];

        return view('examiner.badge', $data);
    }

    /**
     * Show attendance confirmation page after QR scan (Examiner-specific version)
     */
    public function showExaminerAttendanceConfirmation($examiner_id)
    {
        $examiner = User::getExaminers()->firstWhere('examin_id', $examiner_id);

        if (!$examiner) {
            return redirect('examiner/dashboard')->with('error', 'Examiner not found');
        }

        // Check if already registered today
        $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        $data = [
            'header_title' => 'Confirm Attendance Registration',
            'examiner' => $examiner,
            'already_registered' => $existingAttendance ? true : false,
            'registration_time' => $existingAttendance ? $existingAttendance->created_at->format('H:i:s') : null
        ];

        return view('examiner.confirm_attendance', $data);
    }

    /**
     * Process attendance registration after confirmation (Examiner-specific version)
     */
    public function confirmExaminerAttendanceRegistration(Request $request, $examiner_id)
    {
        try {
            $examiner = User::getExaminers()->firstWhere('examin_id', $examiner_id);

            if (!$examiner) {
                return redirect()->back()->with('error', 'Examiner not found');
            }

            // Check for duplicate attendance (same examiner same day)
            $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existingAttendance) {
                return redirect()->back()->with(
                    'info',
                    'Attendance already recorded for today at ' . $existingAttendance->created_at->format('H:i:s')
                );
            }

            // Create new attendance record
            $attendance = Attendance::create([
                'user_id'        => $examiner->user_id ?? null,
                'examiner_id'    => $examiner->examin_id ?? null,
                'country_id'     => $examiner->country_id ?? null,
                'group_id'       => $examiner->group_id ?? null,
                'mobile'         => $examiner->mobile ?? null,
                'specialty'      => $examiner->specialty ?? null,
                'subspecialty'   => $examiner->subspecialty ?? null,
                'shift'          => $examiner->shift ?? null,
                'curriculum_vitae' => $examiner->curriculum_vitae ?? null,
                'passport_image' => $examiner->passport_image ?? null,
            ]);

            return redirect()->back()->with(
                'success',
                'Attendance registered successfully for ' . $examiner->examiner_name . ' at ' . $attendance->created_at->format('H:i:s')
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error registering attendance: ' . $e->getMessage());
        }
    }

    // ── Email template editor ─────────────────────────────────────────────────

    public function emailTemplate()
    {
        $template = DB::table('email_templates')->where('key', 'examiner_bulk')->first();
        return view('admin.exams.email_template', compact('template'));
    }

    public function saveEmailTemplate(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        DB::table('email_templates')->updateOrInsert(
            ['key' => 'examiner_bulk'],
            ['subject' => $request->subject, 'body' => $request->body, 'updated_at' => now()]
        );

        return redirect()->back()->with('success', 'Email template saved successfully.');
    }

    // ── Bulk email to examiners ───────────────────────────────────────────────

    public function sendBulkEmail(Request $request)
    {
        $request->validate([
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string',
            'examiner_ids' => 'required|array|min:1',
        ]);

        $exmIds = $request->examiner_ids;

        $recipients = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->whereIn('examiners.id', $exmIds)
            ->where('users.user_type', 9)
            ->select('users.email', 'users.name')
            ->get();

        $sent   = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            try {
                \Illuminate\Support\Facades\Mail::to($recipient->email, $recipient->name)
                    ->send(new \App\Mail\ExaminerBulkMail(
                        $recipient->name,
                        $request->subject,
                        $request->body
                    ));
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                \Log::error("Examiner email failed to {$recipient->email}: " . $e->getMessage());
            }
        }

        $msg = "Email sent to {$sent} examiner(s).";
        if ($failed) $msg .= " {$failed} failed (check logs).";

        return redirect()->back()->with('success', $msg);
    }

    // ── Public examiner availability form ────────────────────────────────────

    /**
     * Show the public availability confirmation form.
     * No authentication required — the URL can be shared directly with examiners.
     */
    public function availabilityForm()
    {
        $year = date('Y');

        // All active examiners for the dropdown
        $examiners = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->where('users.user_type', 9)
            ->where('user_roles.is_active', 1)
            ->orderBy('users.name')
            ->select('examiners.id as exm_id', 'users.name', 'examiners.examiner_id', 'examiners.specialty')
            ->get();

        return view('public.examiner_availability', compact('year', 'examiners'));
    }

    /**
     * Process the submitted availability form.
     */
    public function availabilitySubmit(Request $request)
    {
        $request->validate([
            'exm_id'            => 'required|integer|exists:examiners,id',
            'exam_availability' => 'required|array|min:1',
            'mcs_shift'         => 'nullable|in:1,2,3',
        ]);

        $examinerRecord = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->where('examiners.id', $request->exm_id)
            ->select('examiners.id as exm_id', 'users.name as examiner_name')
            ->first();

        $availability = $request->exam_availability;
        // Singleton options override everything else
        if (in_array('Not Available', $availability)) {
            $availability = ['Not Available'];
        } elseif (in_array('Tentative', $availability)) {
            $availability = ['Tentative'];
        }

        ExaminerHistory::updateOrCreate(
            ['exm_id' => $examinerRecord->exm_id],
            ['exam_availability' => json_encode($availability)]
        );

        // Save MCS shift preference when MCS is selected
        if (in_array('MCS', $availability) && $request->filled('mcs_shift')) {
            $yearId = User::getCurrentYearId();
            DB::table('exams_shifts')->updateOrInsert(
                ['exm_id' => $examinerRecord->exm_id, 'year_id' => $yearId],
                ['shift' => $request->mcs_shift, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return back()->with('success', 'Thank you, ' . $examinerRecord->examiner_name . '! Your availability for the ' . date('Y') . ' examination has been recorded.');
    }

    public function examinerChangePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return redirect()->back()->with('error', 'Old password is incorrect');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    public function examinerEdit($id)
    {
        // Get the current logged-in examiner
        $currentExaminer = Auth::user();

        // Security check: make sure examiner can only edit their own profile
        $examiner = User::getExaminers()->where('examin_id', $id)->first();

        if (!$examiner || $examiner->user_id != $currentExaminer->id) {
            return redirect('examiner/profile_settings')->with('error', 'Unauthorized access');
        }

        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Profile";
        $data['examiner'] = $examiner;

        // Retrieve all groups and pass them to the view
        $data['groups'] = DB::table('examiners_groups')->select('id', 'group_name')->get();

        return view('examiner.edit_info', $data);
    }

    public function examinerUpdate(Request $request, $id)
    {
        $currentExaminer = Auth::user();
        $examiner = ExamsModel::find($id);

        if (!$examiner) {
            return redirect('examiner/profile_settings')->with('error', 'Examiner not found');
        }

        if ($examiner->user_id != $currentExaminer->id) {
            return redirect('examiner/profile_settings')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|min:6',
            'gender' => 'nullable|in:Male,Female',
            'curriculum_vitae' => 'nullable|file|mimes:pdf,doc,docx|max:3072', // 3MB = 3072 KB
            'passport_image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024', // 1MB = 1024 KB
            'examiner_id' => 'nullable|string|max:255',
            'group_id' => 'nullable|integer',
            'specialty' => 'nullable|string|max:255',
            'subspecialty' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'mobile' => 'nullable|string|max:20',
            'exam_availability' => 'nullable|array',
            'exam_availability.*' => 'in:MCS,FCS,Not Available',
            'shift' => 'nullable|in:1,2,3',
            'virtual_mcs_participated' => 'nullable|in:Yes,No',
            'fcs_participated' => 'nullable|in:Yes,No',
            'participation_type' => 'nullable|in:Examiner,Observer,None',
            'hospital_type' => 'nullable|in:Teaching Hospital,Non Teaching',
            'hospital_name' => 'nullable|string|max:255',
            'examination_years' => 'nullable|array',
            'examination_years.*' => 'in:2020,2021,2022,2023,2024',
        ], [
            // Custom error messages
            'curriculum_vitae.max' => 'The CV file must not be larger than 3MB.',
            'passport_image.max' => 'The profile image must not be larger than 1MB.',
            'curriculum_vitae.mimes' => 'The CV must be a PDF, DOC, or DOCX file.',
            'passport_image.mimes' => 'The profile image must be a JPEG, PNG, or JPG file.',
        ]);

        // dd(request()->all());

        try {
            \DB::beginTransaction();

            $user = User::find($examiner->user_id);
            if ($user) {
                $user->name = $validated['name'];
                $user->email = $validated['email'];

                if (!empty($validated['password'])) {
                    $user->password = Hash::make($validated['password']);
                }
                $user->save();
            }

            // ✅ Upload CV with user ID prefix
            if ($request->hasFile('curriculum_vitae')) {
                if ($examiner->curriculum_vitae && Storage::disk('public')->exists($examiner->curriculum_vitae)) {
                    Storage::disk('public')->delete($examiner->curriculum_vitae);
                }

                $file = $request->file('curriculum_vitae');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $sanitizedName = Str::slug($originalName);
                $extension = $file->getClientOriginalExtension();
                $finalName = $currentExaminer->id . '-' . $sanitizedName . '.' . $extension;

                $path = $file->storeAs('documents/cvs', $finalName, 'public');
                $examiner->curriculum_vitae = $path;
            }

            // ✅ Upload passport image with user ID prefix
            if ($request->hasFile('passport_image')) {
                if ($examiner->passport_image && Storage::disk('public')->exists($examiner->passport_image)) {
                    Storage::disk('public')->delete($examiner->passport_image);
                }

                $file = $request->file('passport_image');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $sanitizedName = Str::slug($originalName);
                $extension = $file->getClientOriginalExtension();
                $finalName = $currentExaminer->id . '-' . $sanitizedName . '.' . $extension;

                $passportPath = $file->storeAs('documents/passports', $finalName, 'public');
                $examiner->passport_image = $passportPath;
            }

            $examiner->gender = $validated['gender'] ?? $examiner->gender;
            $examiner->examiner_id = $validated['examiner_id'] ?? $examiner->examiner_id;
            $examiner->country_id = $validated['country_id'];
            $examiner->mobile = $validated['mobile'] ?? $examiner->mobile;
            $examiner->specialty = $validated['specialty'] ?? $examiner->specialty;
            $examiner->subspecialty = $validated['subspecialty'] ?? $examiner->subspecialty;

            if (isset($validated['participation_type'])) {
                if ($validated['participation_type'] === 'Examiner') {
                    $examiner->role_id = 1;
                } elseif ($validated['participation_type'] === 'Observer') {
                    $examiner->role_id = 2;
                } else {
                    $examiner->role_id = 3;
                }
            }

            $examiner->save();

            // Examiner history
            $historyData = [];

            if ($request->has('exam_availability') && is_array($request->exam_availability)) {
                $availability = $request->exam_availability;

                // If "Not Available" is selected, ignore all others
                if (in_array('Not Available', $availability)) {
                    $availability = ['Not Available'];
                }

                $historyData['exam_availability'] = json_encode($availability);
            }

            if (isset($validated['virtual_mcs_participated'])) {
                $historyData['virtual_mcs_participated'] = $validated['virtual_mcs_participated'];
            }

            if (isset($validated['fcs_participated'])) {
                $historyData['fcs_participated'] = $validated['fcs_participated'];
            }

            if (isset($validated['hospital_type'])) {
                $historyData['hospital_type'] = $validated['hospital_type'];
            }

            if (isset($validated['hospital_name'])) {
                $historyData['hospital_name'] = $validated['hospital_name'];
            }

            if (isset($validated['examination_years'])) {
                $historyData['examination_years'] = json_encode($validated['examination_years']);
            }

            if (!empty($historyData)) {
                \App\Models\ExaminerHistory::updateOrCreate(
                    ['exm_id' => $examiner->id],
                    $historyData
                );
            }

            $currentYear = User::getCurrentYearId();

            if (isset($validated['group_id'])) {
                \DB::table('exams_groups')
                    ->where('exm_id', $examiner->id)
                    ->where('year_id', $currentYear)
                    ->delete();

                \DB::table('exams_groups')->insert([
                    'exm_id' => $examiner->id,
                    'group_id' => $validated['group_id'],
                    'year_id' => $currentYear,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if (isset($validated['shift'])) {
                \DB::table('exams_shifts')
                    ->where('exm_id', $examiner->id)
                    ->where('year_id', $currentYear)
                    ->delete();

                \DB::table('exams_shifts')->insert([
                    'exm_id' => $examiner->id,
                    'year_id' => $currentYear,
                    'shift' => $request->filled('shift') ? $validated['shift'] : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            \DB::commit();
            return redirect('examiner/profile_settings')->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Examiner update failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the profile. Please try again. Error: ' . $e->getMessage());
        }
    }
}
