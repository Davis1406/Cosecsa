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
    public function list(Request $request)
    {
        $currentYearId = User::getCurrentYearId();

        $allExamYears = DB::table('years')->orderByDesc('id')->get(['id', 'year_name']);

        // '' or absent → show all (no year filter); integer → specific year
        $yearIdInput     = $request->input('year_id');
        $noYearSelected  = ($yearIdInput === null || $yearIdInput === '');
        $requestedYearId = $noYearSelected ? null : (int)$yearIdInput;

        $hasParticipations = \Illuminate\Support\Facades\Schema::hasTable('examiner_participations');

        // all_examined_for: always spans ALL years — used to populate the Programmes
        // dropdown and for client-side filtering regardless of the selected year.
        $allExaminedForSql = '(
            SELECT GROUP_CONCAT(DISTINCT spec ORDER BY spec SEPARATOR ", ")
            FROM (
                SELECT "MCS" as spec FROM mcs_results
                    WHERE mcs_results.examiner_id = examiners.id
                UNION ALL
                SELECT "FCS General Surgery" FROM gs_results
                    WHERE gs_results.examiner_id = examiners.id' .
            ($hasParticipations ? '
                UNION ALL
                SELECT ep.specialty FROM examiner_participations ep
                    WHERE ep.exm_id = examiners.id AND ep.specialty IS NOT NULL' : '') . '
            ) specs
        ) as all_examined_for';

        if ($noYearSelected) {
            // Default the participation button to the previous year even when "All Years" is shown
            $prevYearRow  = $allExamYears->firstWhere('id', $currentYearId - 1);
            $lastYearId   = $prevYearRow ? $prevYearRow->id        : ($currentYearId - 1);
            $lastYearName = $prevYearRow ? $prevYearRow->year_name : (string)(date('Y') - 1);

            $participatedSql = "CASE WHEN MAX(examiners_history.examination_years) LIKE '%{$lastYearName}%'
                                THEN 1 ELSE 0 END as participated_last_year";

            // Display column aggregates all years when no specific year is selected
            $examinedForSql  = '(
                SELECT GROUP_CONCAT(DISTINCT spec ORDER BY spec SEPARATOR ", ")
                FROM (
                    SELECT "MCS" as spec FROM mcs_results
                        WHERE mcs_results.examiner_id = examiners.id
                    UNION ALL
                    SELECT "FCS General Surgery" FROM gs_results
                        WHERE gs_results.examiner_id = examiners.id' .
                ($hasParticipations ? '
                    UNION ALL
                    SELECT ep.specialty FROM examiner_participations ep
                        WHERE ep.exm_id = examiners.id AND ep.specialty IS NOT NULL' : '') . '
                ) specs
            ) as examined_for';
        } else {
            $selectedYearRow = $allExamYears->firstWhere('id', $requestedYearId);
            $lastYearId      = $selectedYearRow ? $selectedYearRow->id        : $requestedYearId;
            $lastYearName    = $selectedYearRow ? $selectedYearRow->year_name : (string)$requestedYearId;

            $participatedSql = "CASE WHEN MAX(examiners_history.examination_years) LIKE '%{$lastYearName}%'
                                THEN 1 ELSE 0 END as participated_last_year";

            $examinedForSql = '(
                SELECT GROUP_CONCAT(DISTINCT spec ORDER BY spec SEPARATOR ", ")
                FROM (
                    SELECT "MCS" as spec FROM mcs_results
                        WHERE mcs_results.examiner_id = examiners.id AND mcs_results.exam_year = ' . $lastYearId . '
                    UNION ALL
                    SELECT "FCS General Surgery" FROM gs_results
                        WHERE gs_results.examiner_id = examiners.id AND gs_results.exam_year = ' . $lastYearId .
                ($hasParticipations ? '
                        AND NOT EXISTS (
                            SELECT 1 FROM examiner_participations ep2
                            WHERE ep2.exm_id = examiners.id AND ep2.year_id = ' . $lastYearId . ' AND ep2.specialty IS NOT NULL
                        )
                    UNION ALL
                    SELECT ep.specialty FROM examiner_participations ep
                        WHERE ep.exm_id = examiners.id AND ep.year_id = ' . $lastYearId . ' AND ep.specialty IS NOT NULL' : '') . '
                ) specs
            ) as examined_for';
        }

        $examiners = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('countries', 'countries.id', '=', 'examiners.country_id')
            ->leftJoin('examiners_history', 'examiners_history.exm_id', '=', 'examiners.id')
            ->leftJoin('exams_groups', function ($join) use ($currentYearId) {
                $join->on('exams_groups.exm_id', '=', 'examiners.id')
                     ->where('exams_groups.year_id', $currentYearId);
            })
            ->leftJoin('examiners_groups', 'examiners_groups.id', '=', 'exams_groups.group_id')
            ->leftJoin('examiners_roles', 'examiners_roles.id', '=', 'examiners.role_id')
            ->where('users.user_type', 9)
            ->select(
                'examiners.id as id',
                'examiners.id as examin_id',
                'examiners.user_id as ex_id',
                'examiners.examiner_id',
                'examiners.examiner_designation',
                'examiners.role_id',
                'examiners_roles.role as role_name',
                'users.name as examiner_name',
                'users.email',
                'countries.country_name',
                'examiners.internal_notes',
                DB::raw('GROUP_CONCAT(DISTINCT examiners_groups.group_name ORDER BY examiners_groups.group_name SEPARATOR ", ") as group_name'),
                DB::raw($participatedSql),
                DB::raw($examinedForSql),
                DB::raw($allExaminedForSql)
            )
            ->groupBy(
                'examiners.id', 'examiners.user_id', 'examiners.examiner_id',
                'examiners.examiner_designation', 'examiners.role_id', 'examiners_roles.role',
                'users.name', 'users.email', 'countries.country_name', 'examiners.internal_notes'
            )
            ->orderBy('users.name')
            ->get();

        // Build filter options from the already-fetched collection
        $countries    = $examiners->pluck('country_name')->filter()->unique()->sort()->values();
        // Programmes always built from all-years data so the dropdown is complete
        $programmes   = $examiners->pluck('all_examined_for')->filter()
            ->flatMap(fn($v) => array_map('trim', explode(',', $v)))
            ->unique()->sort()->values();
        $designations = $examiners->pluck('examiner_designation')->filter()->unique()->sort()->values();

        // Designation options for the filter (from DB; fall back to what's in use)
        $designationOptions = \Illuminate\Support\Facades\Schema::hasTable('designation_options')
            ? DB::table('designation_options')->orderBy('sort_order')->orderBy('name')->pluck('name')
            : $designations;

        // Build role options from the already-fetched collection
        $roleOptions = $examiners->pluck('role_name')->filter()->unique()
            ->map(fn($r) => ucfirst($r))->sort()->values();

        $data['getExaminers']       = $examiners;
        $data['countries']          = $countries;
        $data['programmes']         = $programmes;
        $data['designationOptions'] = $designationOptions;
        $data['roleOptions']        = $roleOptions;
        $data['allExamYears']       = $allExamYears;
        $data['selectedExamYear']   = $lastYearName;
        $data['selectedYearId']     = $lastYearId;
        $data['noYearSelected']     = $noYearSelected;
        $data['currentYear']        = date('Y');
        $data['lastYear']           = $lastYearName;
        $data['header_title']       = 'Examiners';

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
        $yearId   = User::getCurrentYearId();
        $lastYear = DB::table('years')->where('id', $yearId - 1)->value('year_name') ?? (date('Y') - 1);

        $designationOptions = \Illuminate\Support\Facades\Schema::hasTable('designation_options')
            ? DB::table('designation_options')->orderBy('sort_order')->orderBy('name')->pluck('name')->toArray()
            : ['Court of Examiner', 'Panel Head', 'Other'];

        $data['getCountry']         = Country::getCountry();
        $data['header_title']       = "Add New Examiner";
        $data['groups']             = DB::table('examiners_groups')->select('id', 'group_name')->get();
        $data['examYears']          = range(2020, (int) $lastYear);
        $data['programmeOptions']   = self::$programmeOptions;
        $data['designationOptions'] = $designationOptions;
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
                'user_id'              => $user->id,
                'examiner_id'          => $request->examiner_id,
                'country_id'           => $request->country_id,
                'mobile'               => $request->mobile,
                'specialty'            => $request->specialty,
                'subspecialty'         => $request->subspecialty,
                'gender'               => $request->gender,
                'role_id'              => $request->participation_type === 'Examiner' ? 1 : ($request->participation_type === 'Observer' ? 2 : 3),
                'examiner_designation' => $request->examiner_designation ?: null,
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
                'exm_id'                    => $examiner->id,
                'virtual_mcs_participated'  => $request->virtual_mcs_participated ?? null,
                'fcs_participated'          => $request->fcs_participated ?? null,
                'participation_type'        => $request->participation_type,
                'hospital_type'             => $request->hospital_type ?? null,
                'hospital_name'             => $request->hospital_name ?? null,
                'exam_availability'         => $availability,
                'availability_year_id'      => User::getCurrentYearId(),
                'examination_years'         => $request->examination_years ?? null,
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
        $yearId = User::getCurrentYearId();

        // Single targeted query — avoids loading all 700+ examiners via User::getExaminers()
        $examiner = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('countries', 'countries.id', '=', 'examiners.country_id')
            ->leftJoin('exams_groups', function ($join) use ($yearId) {
                $join->on('exams_groups.exm_id', '=', 'examiners.id')
                     ->where('exams_groups.year_id', $yearId);
            })
            ->leftJoin('exams_shifts', function ($join) use ($yearId) {
                $join->on('exams_shifts.exm_id', '=', 'examiners.id')
                     ->where('exams_shifts.year_id', $yearId);
            })
            ->where('examiners.id', $id)
            ->select(
                'examiners.id as id',
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
                'examiners.examiner_designation',
                'users.name as examiner_name',
                'users.email',
                'countries.country_name',
                'exams_groups.group_id',
                'exams_shifts.shift as shift_id'
            )
            ->first();

        if (!$examiner) return redirect()->back()->with('error', 'Examiner not found');

        // Attach history (single row lookup)
        $history = DB::table('examiners_history')->where('exm_id', $id)->first();

        // Clear exam_availability if it was saved for a different year —
        // the examiner has not yet confirmed their availability for this year.
        if ($history && ($history->availability_year_id ?? 0) != $yearId) {
            $history->exam_availability = null;
        }

        $examiner->history                    = $history;
        $examiner->virtual_mcs_participated   = $history->virtual_mcs_participated ?? null;
        $examiner->fcs_participated           = $history->fcs_participated ?? null;
        $examiner->hospital_type              = $history->hospital_type ?? null;
        $examiner->hospital_name              = $history->hospital_name ?? null;
        $examiner->examination_years          = $history->examination_years ?? null;

        // Back URL
        $from    = $request->input('from');
        $backUrl = null;
        if ($from) {
            $query   = $request->except(['from', '_token']);
            $backUrl = url($from) . (count($query) ? '?' . http_build_query($query) : '');
        } else {
            $referer = $request->header('referer');
            $backUrl = ($referer && str_contains($referer, url('/')))
                ? $referer
                : url('admin/exams/examiners');
        }

        // Dynamic year list: 2020 → last completed year
        $currentYearName = DB::table('years')->where('id', $yearId)->value('year_name') ?? date('Y');
        $lastYearName    = DB::table('years')->where('id', $yearId - 1)->value('year_name') ?? (date('Y') - 1);
        $examYears       = range(2020, (int) $lastYearName);

        // Load year → [programmes] and year → [programme => role] from examiner_participations
        $yearParticipations = [];  // ['2024' => ['MCS', 'FCS Plastic Surgery']]
        $yearRoles          = [];  // ['2024' => ['MCS' => 'Examiner', 'FCS Plastic Surgery' => 'Observer']]

        DB::table('examiner_participations')
            ->join('years', 'years.id', '=', 'examiner_participations.year_id')
            ->where('examiner_participations.exm_id', $id)
            ->whereNotNull('examiner_participations.specialty')
            ->select('years.year_name', 'examiner_participations.specialty', 'examiner_participations.role')
            ->get()
            ->each(function ($row) use (&$yearParticipations, &$yearRoles) {
                $yearParticipations[(string)$row->year_name][] = $row->specialty;
                $yearRoles[(string)$row->year_name][$row->specialty] = $row->role ?: 'Examiner';
            });

        // Canonical programme list (shared static list)
        $programmeOptions = self::$programmeOptions;

        // Designation options — DB-driven with hardcoded fallback
        $designationOptions = \Illuminate\Support\Facades\Schema::hasTable('designation_options')
            ? DB::table('designation_options')->orderBy('sort_order')->orderBy('name')->pluck('name')->toArray()
            : ['Court of Examiner', 'Panel Head', 'Other'];

        return view('admin.exams.edit_examiner', [
            'header_title'       => 'Edit Examiner',
            'examiner'           => $examiner,
            'getCountry'         => Country::getCountry(),
            'groups'             => DB::table('examiners_groups')->select('id', 'group_name')->get(),
            'backUrl'            => $backUrl,
            'examYears'          => $examYears,
            'currentYearName'    => $currentYearName,
            'yearParticipations' => $yearParticipations,
            'yearRoles'          => $yearRoles,
            'programmeOptions'   => $programmeOptions,
            'designationOptions' => $designationOptions,
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

            // ── Upload CV ────────────────────────────────────────────────────
            if ($request->hasFile('curriculum_vitae')) {
                if ($examiner->curriculum_vitae && Storage::disk('public')->exists($examiner->curriculum_vitae)) {
                    Storage::disk('public')->delete($examiner->curriculum_vitae);
                }
                $file        = $request->file('curriculum_vitae');
                $sanitized   = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $finalName   = $examiner->id . '-' . $sanitized . '.' . $file->getClientOriginalExtension();
                $examiner->curriculum_vitae = $file->storeAs('documents/cvs', $finalName, 'public');
            }

            // ── Upload passport / profile photo ──────────────────────────────
            if ($request->hasFile('passport_image')) {
                if ($examiner->passport_image && Storage::disk('public')->exists($examiner->passport_image)) {
                    Storage::disk('public')->delete($examiner->passport_image);
                }
                $file        = $request->file('passport_image');
                $sanitized   = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $finalName   = $examiner->id . '-' . $sanitized . '.' . $file->getClientOriginalExtension();
                $examiner->passport_image = $file->storeAs('documents/passports', $finalName, 'public');
            }

            // Update examiner info
            $examiner->update([
                'gender'               => $request->gender,
                'examiner_id'          => $request->examiner_id,
                'country_id'           => $request->country_id,
                'mobile'               => $request->mobile,
                'specialty'            => $request->specialty,
                'subspecialty'         => $request->subspecialty,
                'role_id'              => $request->participation_type === 'Examiner' ? 1 : ($request->participation_type === 'Observer' ? 2 : 3),
                'examiner_designation' => $request->examiner_designation ?: null,
                'curriculum_vitae'     => $examiner->curriculum_vitae,
                'passport_image'       => $examiner->passport_image,
            ]);

            // Update user_roles
            DB::table('user_roles')
                ->where('user_id', $user->id)
                ->where('role_type', 9)
                ->update([
                    'updated_at' => now(),
                    'is_active' => 1,
                ]);

            // Update group — only insert when a real group was selected
            DB::table('exams_groups')
                ->where('exm_id', $examiner->id)
                ->where('year_id', User::getCurrentYearId())
                ->delete();
            if ($request->filled('group_id')) {
                DB::table('exams_groups')->insert([
                    'exm_id'     => $examiner->id,
                    'group_id'   => $request->group_id,
                    'year_id'    => User::getCurrentYearId(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Update shift — only insert when a real shift was selected
            ExamsShift::where('exm_id', $examiner->id)
                ->where('year_id', User::getCurrentYearId())
                ->delete();
            if ($request->filled('shift')) {
                ExamsShift::create([
                    'exm_id'  => $examiner->id,
                    'shift'   => $request->shift,
                    'year_id' => User::getCurrentYearId(),
                ]);
            }

            // Build history update payload — base fields always updated
            $historyData = [
                'virtual_mcs_participated' => $request->virtual_mcs_participated ?? null,
                'fcs_participated'         => $request->fcs_participated ?? null,
                'participation_type'       => $request->participation_type,
                'hospital_type'            => $request->hospital_type ?? null,
                'hospital_name'            => $request->hospital_name ?? null,
                'examination_years'        => $request->examination_years ?? null,
            ];

            // Only write exam_availability when the admin explicitly checked boxes.
            // Unchecked checkboxes send nothing, so $request->has() is false → keep existing.
            if ($request->has('exam_availability')) {
                $availability = $request->exam_availability;
                if (in_array('Not Available', $availability)) {
                    $availability = ['Not Available'];
                }
                $historyData['exam_availability']    = $availability;
                $historyData['availability_year_id'] = User::getCurrentYearId();
            }

            ExaminerHistory::updateOrCreate(['exm_id' => $examiner->id], $historyData);

            // ── Sync examiner_participations from year+programme checkboxes ──────
            // $request->year_programme = ['2024' => ['MCS'], '2025' => ['FCS Urology','MCS']]
            // $request->year_role      = ['2024' => ['MCS' => 'Examiner'], '2025' => ['MCS' => 'Observer', ...]]
            $this->syncParticipations($examiner->id, $request->examination_years ?? [], $request->year_programme ?? [], $request->year_role ?? []);

            DB::commit();

            // Always return to the examiners list after a successful edit.
            return redirect('admin/exams/examiners')->with('success', 'Examiner updated successfully');
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
                'examiners.internal_notes',
                'examiners.examiner_designation',
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

        // Clear exam_availability if it was saved for a different year
        if ($history && ($history->availability_year_id ?? 0) != $yearId) {
            $history->exam_availability = null;
        }

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

        $currentYearName = DB::table('years')->where('id', $yearId)->value('year_name') ?? date('Y');

        // Build per-year programme+role map.
        $hasParticipations = \Illuminate\Support\Facades\Schema::hasTable('examiner_participations');
        $yearProgrammes = [];   // ['2024' => ['MCS', 'FCS Plastic Surgery']]
        $yearRoles      = [];   // ['2024' => ['MCS' => 'Examiner', 'FCS Plastic Surgery' => 'Observer']]

        $rawYears = $history->examination_years ?? null;
        $decodedYears = json_decode($rawYears, true);
        if (is_string($decodedYears)) { $decodedYears = json_decode($decodedYears, true); }
        $examinedYears = is_array($decodedYears) ? $decodedYears : [];

        // Pre-load all examiner_participations rows (specialty + role) for this examiner
        $allEP = [];  // [year_name => [specialty => role]]
        if ($hasParticipations) {
            $epRows = DB::table('examiner_participations')
                ->join('years', 'years.id', '=', 'examiner_participations.year_id')
                ->where('examiner_participations.exm_id', $id)
                ->whereNotNull('examiner_participations.specialty')
                ->select('years.year_name', 'examiner_participations.specialty', 'examiner_participations.role')
                ->get();
            foreach ($epRows as $row) {
                $allEP[(string)$row->year_name][$row->specialty] = $row->role ?: null;
            }
        }

        $defaultRole = $examiner->role_id == 1 ? 'Examiner' : 'Observer';

        foreach ($examinedYears as $yearName) {
            $yearRow = DB::table('years')->where('year_name', (string)$yearName)->first();
            if (!$yearRow) continue;
            $yid = $yearRow->id;

            $programmes = [];
            $roles      = [];

            // ── From examiner_participations (primary; has per-programme roles) ──
            if (!empty($allEP[(string)$yearName])) {
                foreach ($allEP[(string)$yearName] as $spec => $role) {
                    $programmes[] = $spec;
                    $roles[$spec] = $role ?? $defaultRole;
                }
            }

            // ── MCS from mcs_results if not already tracked ─────────────────
            if (!in_array('MCS', $programmes)) {
                if (DB::table('mcs_results')->where('examiner_id', $id)->where('exam_year', $yid)->exists()) {
                    $programmes[] = 'MCS';
                    $roles['MCS'] = $defaultRole;
                }
            }

            // ── FCS General Surgery from gs_results if no FCS row yet ───────
            $hasFCS = !empty(array_filter($programmes, fn($p) => stripos($p, 'FCS') !== false));
            if (!$hasFCS) {
                if (DB::table('gs_results')->where('examiner_id', $id)->where('exam_year', $yid)->exists()) {
                    $programmes[] = 'FCS General Surgery';
                    $roles['FCS General Surgery'] = $defaultRole;
                }
            }

            $yearProgrammes[(string)$yearName] = array_unique($programmes);
            $yearRoles[(string)$yearName]      = $roles;
        }

        // Year range for the Manage Participation modal (2020 → last completed year)
        $lastYearName2  = DB::table('years')->where('id', $yearId - 1)->value('year_name') ?? (date('Y') - 1);
        $examYears      = range(2020, (int) $lastYearName2);

        // ── Candidates Examined by this examiner (all years, all programmes) ──
        $resultTables = [
            'mcs_results'                   => 'MCS',
            'gs_results'                    => 'FCS General Surgery',
            'cardiothoracic_results'        => 'FCS Cardiothoracic',
            'urology_results'               => 'FCS Urology',
            'paediatric_results'            => 'FCS Paediatric Surgery',
            'ent_results'                   => 'FCS ENT',
            'plastic_surgery_results'       => 'FCS Plastic Surgery',
            'neurosurgery_results'          => 'FCS Neurosurgery',
            'orthopaedic_results'           => 'FCS Orthopaedic Surgery',
            'paediatric_orthopaedics_results' => 'FCS Paediatric Orthopaedics',
        ];

        $candidatesExamined = collect();
        foreach ($resultTables as $table => $programme) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) continue;
            $cols = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            $yearCol = in_array('exam_year', $cols) ? 'exam_year' : null;
            if (!in_array('examiner_id', $cols) || !in_array('candidate_id', $cols)) continue;

            $q = DB::table($table)
                ->join('candidates', "$table.candidate_id", '=', 'candidates.id')
                ->where("$table.examiner_id", $id)
                ->select(
                    'candidates.id as candidate_id',
                    DB::raw("CONCAT(COALESCE(candidates.firstname,''),' ',COALESCE(candidates.middlename,''),' ',COALESCE(candidates.lastname,'')) as candidate_name"),
                    'candidates.candidate_id as candidate_no',
                    DB::raw("'$programme' as programme"),
                    DB::raw($yearCol ? "MAX($table.$yearCol) as exam_year" : "NULL as exam_year")
                )
                ->groupBy('candidates.id', 'candidates.firstname', 'candidates.middlename', 'candidates.lastname', 'candidates.candidate_id');

            $rows = $q->get();
            $candidatesExamined = $candidatesExamined->merge($rows);
        }

        // Resolve year names for display (exam_year stored as years.id in FCS, as year string in MCS/GS)
        $yearsMap = DB::table('years')->pluck('year_name', 'id')->toArray();
        $candidatesExamined = $candidatesExamined->map(function($row) use ($yearsMap) {
            if ($row->exam_year && isset($yearsMap[$row->exam_year])) {
                $row->exam_year_display = $yearsMap[$row->exam_year];
            } else {
                $row->exam_year_display = $row->exam_year ?? '—';
            }
            $row->candidate_name = trim(preg_replace('/\s+/', ' ', $row->candidate_name));
            return $row;
        })->sortByDesc('exam_year')->values();

        return view('admin.exams.view_examiner', [
            'header_title'     => 'View Examiner',
            'examiner'         => $examiner,
            'getCountry'       => Country::getCountry(),
            'groups'           => DB::table('examiners_groups')->select('id', 'group_name')->get(),
            'qrCode'           => $qrCode,
            'backUrl'          => $backUrl,
            'yearProgrammes'   => $yearProgrammes,
            'yearRoles'        => $yearRoles,
            'currentYearName'  => $currentYearName,
            'examYears'        => $examYears,
            'exYears'              => $examinedYears,
            'programmeOptions'     => self::$programmeOptions,
            'candidatesExamined'   => $candidatesExamined,
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
     * Soft-delete: clear this year's availability submission only.
     * Hard-delete: remove the full examiners_history row + examiner_participations.
     */
    public function resetExaminerConfirmation(Request $request, $id)
    {
        $type = $request->input('type', 'soft'); // 'soft' | 'hard'
        $back = $request->input('back', url('admin/exams/examiner-confirmation'));

        $examiner = DB::table('examiners')->where('id', $id)->first();
        if (!$examiner) {
            return redirect($back)->with('error', 'Examiner not found.');
        }

        if ($type === 'hard') {
            DB::table('examiners_history')->where('exm_id', $id)->delete();
            DB::table('examiner_participations')->where('exm_id', $id)->delete();
            return redirect($back)->with('success', 'Confirmation history fully deleted.');
        }

        // Soft: clear exam_availability and availability_year_id.
        // Always succeed — if already null, the desired state is already met.
        DB::table('examiners_history')
            ->where('exm_id', $id)
            ->update([
                'exam_availability'    => null,
                'availability_year_id' => null,
                'updated_at'           => now(),
            ]);

        return redirect($back)->with('success', 'Availability confirmation cleared successfully.');
    }

    /**
     * Soft-delete: deactivate user role only (keeps all data).
     * Hard-delete: remove examiners + all related data (history, participations,
     *              groups, shifts, attendance). User account is kept unless no
     *              other roles exist.
     */
    public function destroyExaminer(Request $request, $id)
    {
        $type = $request->input('type', 'soft');
        $back = $request->input('back', 'admin/exams/examiners');

        $examiner = DB::table('examiners')->where('id', $id)->first();
        if (!$examiner) {
            return redirect($back)->with('error', 'Examiner not found.');
        }

        $userId = $examiner->user_id;

        if ($type === 'hard') {
            // Remove all linked data
            DB::table('examiners_history')->where('exm_id', $id)->delete();
            DB::table('examiner_participations')->where('exm_id', $id)->delete();
            DB::table('exams_groups')->where('exm_id', $id)->delete();
            DB::table('exams_shifts')->where('exm_id', $id)->delete();
            DB::table('attendance')->where('examiner_id', $id)->delete();
            DB::table('examiners')->where('id', $id)->delete();

            // Remove user role row
            DB::table('user_roles')->where('user_id', $userId)->where('role_type', 9)->delete();

            // Delete user account only if no other role rows remain
            $remainingRoles = DB::table('user_roles')->where('user_id', $userId)->count();
            if ($remainingRoles === 0) {
                DB::table('users')->where('id', $userId)->delete();
            }

            return redirect('admin/exams/examiners')->with('success', 'Examiner and all associated data permanently deleted.');
        }

        // Soft: deactivate
        DB::table('user_roles')
            ->where('user_id', $userId)
            ->where('role_type', 9)
            ->update(['is_active' => 0, 'updated_at' => now()]);

        return redirect($back)->with('success', 'Examiner deactivated (soft deleted).');
    }

    /**
     * Delete a single attendance record.
     */
    public function destroyAttendanceRecord(Request $request, $id)
    {
        $date = $request->input('date', \Carbon\Carbon::today()->toDateString());
        DB::table('attendance')->where('id', $id)->delete();
        return redirect(url('admin/exams/attendance') . '?date=' . $date)
            ->with('success', 'Attendance record deleted.');
    }

    /**
     * Delete all attendance records for a given date.
     */
    public function destroyAttendanceByDate(Request $request)
    {
        $date = $request->input('date');
        if (!$date) {
            return redirect(url('admin/exams/attendance'))->with('error', 'No date specified.');
        }
        $count = DB::table('attendance')->whereDate('created_at', $date)->delete();
        return redirect(url('admin/exams/attendance') . '?date=' . $date)
            ->with('success', "{$count} attendance record(s) deleted for {$date}.");
    }

    /**
     * Generate visual report for examiner confirmations
     */
    public function generateVisualReport(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $filterMode = $request->input('filter', 'all'); // 'all' | 'last_year'

        $getExaminers = $this->getExaminersData($yearId);

        // Filter to participants of the selected year
        // (confirmed for this year AND have the same year in their examination_years history)
        if ($filterMode === 'last_year') {
            $getExaminers = $getExaminers->filter(function ($e) use ($yearName) {
                $years = $e->examination_years ?? null;
                return $years && strpos($years, (string)$yearName) !== false;
            })->values();
        }

        $data = [
            'availabilityData'  => $this->processAvailabilityData($getExaminers),
            'participationData' => $this->processParticipationData($getExaminers),
            'countryData'       => $this->processCountryData($getExaminers),
            'header_title'      => 'Examiner Visual Report',
            'allYears'          => $allYears,
            'selectedYearId'    => $yearId,
            'selectedYearName'  => $yearName,
            'filterMode'        => $filterMode,
            'totalShown'        => $getExaminers->count(),
        ];

        return view('admin.exams.visual_report', $data);
    }

    private function getExaminersData($yearId = null)
    {
        $yearId = $yearId ?? User::getCurrentYearId();

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
                DB::raw('MAX(examiners_history.examination_years) as examination_years'),
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
            ->where(function ($query) use ($yearId) {
                // Only show examiners who are genuinely confirmed for the current year:
                // 1. Submitted the availability form for this year (availability_year_id matches and not null)
                // 2. OR admin assigned them a real shift for this year
                // 3. OR admin assigned them to a real group for this year
                $query->where(function ($q) use ($yearId) {
                          $q->where('examiners_history.availability_year_id', $yearId)
                            ->whereNotNull('examiners_history.exam_availability');
                      })
                      ->orWhereNotNull('exams_shifts.shift')
                      ->orWhereNotNull('exams_groups.group_id');
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

    // ── Shared programme list ─────────────────────────────────────────────────
    private static array $programmeOptions = [
        'MCS',
        'FCS General Surgery',
        'FCS Cardiothoracic Surgery',
        'FCS Urology',
        'FCS Paediatric Surgery',
        'FCS Otorhinolaryngology',
        'FCS Plastic Surgery',
        'FCS Neurosurgery',
        'FCS Orthopaedic Surgery',
        'FCS Paediatric Orthopaedic Surgery',
    ];

    /**
     * Sync examiner_participations for the past years shown in the admin form.
     *
     * @param int   $examinerModelId   examiners.id
     * @param array $selectedYearNames ['2024','2025',…]
     * @param array $yearProgrammes    ['2024'=>['MCS'],'2025'=>['FCS Urology','FCS General Surgery']]
     */
    /**
     * @param array $yearRoles  ['2025' => ['MCS' => 'Examiner', 'FCS Plastic Surgery' => 'Observer']]
     */
    private function syncParticipations(int $examinerModelId, array $selectedYearNames, array $yearProgrammes, array $yearRoles = []): void
    {
        $selectedYearNames = array_map('strval', $selectedYearNames);

        $lastYearName = DB::table('years')
            ->where('id', User::getCurrentYearId() - 1)
            ->value('year_name') ?? (date('Y') - 1);

        // year_name → year_id for every year the form covers.
        // year_name is an ENUM column — must compare with strings, not integers.
        $allFormYears = array_map('strval', range(2020, (int) $lastYearName));
        $formYearIds  = DB::table('years')
            ->whereIn('year_name', $allFormYears)
            ->pluck('id', 'year_name');

        // Delete participation rows for years that were unchecked
        $uncheckedYearIds = $formYearIds
            ->filter(fn($id, $name) => !in_array((string) $name, $selectedYearNames))
            ->values()
            ->toArray();

        if (!empty($uncheckedYearIds)) {
            DB::table('examiner_participations')
                ->where('exm_id', $examinerModelId)
                ->whereIn('year_id', $uncheckedYearIds)
                ->delete();
        }

        // For each checked year: delete existing rows then insert one per selected programme
        foreach ($selectedYearNames as $yearName) {
            $programmes = array_filter((array) ($yearProgrammes[(string) $yearName] ?? []));
            $yearId     = $formYearIds[(string) $yearName] ?? null;
            if (!$yearId) {
                continue;
            }

            // Always delete existing records for this year so we can replace them cleanly
            DB::table('examiner_participations')
                ->where('exm_id', $examinerModelId)
                ->where('year_id', $yearId)
                ->delete();

            foreach ($programmes as $prog) {
                $role = $yearRoles[(string)$yearName][$prog] ?? 'Examiner';
                DB::table('examiner_participations')->insert([
                    'exm_id'     => $examinerModelId,
                    'year_id'    => $yearId,
                    'specialty'  => $prog,
                    'role'       => $role,
                    'source'     => 'manual',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * POST admin/exams/examiner/{id}/upload-cv
     * Upload a CV for an examiner who doesn't have one yet (or replace existing).
     */
    public function uploadCv(Request $request, $id)
    {
        $request->validate([
            'curriculum_vitae' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $examiner = ExamsModel::findOrFail($id);

        // Delete old CV if present
        if ($examiner->curriculum_vitae && Storage::disk('public')->exists($examiner->curriculum_vitae)) {
            Storage::disk('public')->delete($examiner->curriculum_vitae);
        }

        $file      = $request->file('curriculum_vitae');
        $sanitized = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $finalName = $examiner->id . '-' . $sanitized . '.' . $file->getClientOriginalExtension();
        $path      = $file->storeAs('documents/cvs', $finalName, 'public');

        $examiner->curriculum_vitae = $path;
        $examiner->save();

        return redirect()->back()->with('success', 'CV uploaded successfully.');
    }

    /**
     * POST admin/exams/examiner/{id}/memo
     * Save (or clear) the internal notes / memo for an examiner.
     */
    public function saveMemo(Request $request, $id)
    {
        $request->validate([
            'internal_notes' => 'nullable|string|max:5000',
        ]);

        $examiner = ExamsModel::findOrFail($id);
        $examiner->internal_notes = $request->input('internal_notes');
        $examiner->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Memo saved.']);
        }

        return redirect()->back()->with('success', 'Memo saved successfully.');
    }

    /**
     * POST admin/exams/examiner/{id}/upload-photo
     * Replace an examiner's passport / profile photo from the view page.
     */
    public function uploadPhoto(Request $request, $id)
    {
        $request->validate([
            'passport_image' => 'required|file|image|max:5120',
        ]);

        $examiner = ExamsModel::findOrFail($id);

        if ($examiner->passport_image && Storage::disk('public')->exists($examiner->passport_image)) {
            Storage::disk('public')->delete($examiner->passport_image);
        }

        $file      = $request->file('passport_image');
        $sanitized = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $finalName = $examiner->id . '-' . $sanitized . '.' . $file->getClientOriginalExtension();
        $examiner->passport_image = $file->storeAs('documents/passports', $finalName, 'public');
        $examiner->save();

        return redirect()->back()->with('success', 'Profile photo updated successfully.');
    }

    // ── Specialty constants ───────────────────────────────────────────────────
    /** Map messy specialty values → canonical COSECSA programme names */
    const SPECIALTY_MAP = [
        'Cardiothoracic'                          => 'FCS Cardiothoracic Surgery',
        'Cardiothoracic Surgery'                  => 'FCS Cardiothoracic Surgery',
        'Cardiothoracic/General Surgery'          => 'FCS Cardiothoracic Surgery',
        'General Surgery'                         => 'FCS General Surgery',
        'general surgery'                         => 'FCS General Surgery',
        'General'                                 => 'FCS General Surgery',
        'FCS'                                     => 'FCS General Surgery',
        'general surgery/UROLOGY'                 => 'FCS General Surgery',
        'General/ Breast Surgery'                 => 'FCS General Surgery',
        'General/ Critical Care Trauma Surgery'   => 'FCS General Surgery',
        'General/ Gastroenterologist Surgery'     => 'FCS General Surgery',
        'General/ Paediatric Surgery'             => 'FCS General Surgery',
        'General/ Plastic Surgery'                => 'FCS General Surgery',
        'General/ Surgical Oncology Surgery'      => 'FCS General Surgery',
        'General/ Urology Surgery'                => 'FCS General Surgery',
        'General/ Vascular Surgery'               => 'FCS General Surgery',
        'General/HBP/Transplant Surgery'          => 'FCS General Surgery',
        'Colon & Rectal Gen surg'                 => 'FCS General Surgery',
        'Neurosurgery'                            => 'FCS Neurosurgery',
        'Orthopaedic Surgery'                     => 'FCS Orthopaedic Surgery',
        'Orthopaedics'                            => 'FCS Orthopaedic Surgery',
        'ORTHOPEDICS'                             => 'FCS Orthopaedic Surgery',
        'FCS orthopaedics'                        => 'FCS Orthopaedic Surgery',
        'Trauma & Orthopaedic Surgery'            => 'FCS Orthopaedic Surgery',
        'Ortho/P-O'                               => 'FCS Orthopaedic Surgery',
        'Orthopaedic/ Paed-Ortho Surgery'         => 'FCS Orthopaedic Surgery',
        'Otorhinolaryngology'                     => 'FCS Otorhinolaryngology',
        'Otorhinolaryngology(ENT)'                => 'FCS Otorhinolaryngology',
        'Paediatric Surgery'                      => 'FCS Paediatric Surgery',
        'Paediatric'                              => 'FCS Paediatric Surgery',
        'Paediatric  Surgery'                     => 'FCS Paediatric Surgery',
        'FCS Paediatrics'                         => 'FCS Paediatric Surgery',
        'Paediatric Orthopaedic Surgery'          => 'FCS Paediatric Orthopaedic Surgery',
        'Plastic Surgery'                         => 'FCS Plastic Surgery',
        'Urologic Surgery'                        => 'FCS Urologic Surgery',
        'FCS Urology'                             => 'FCS Urologic Surgery',
        'FCS  Urologic Surgery'                   => 'FCS Urologic Surgery',
        'Vascular Surgery'                        => 'FCS General Surgery',
        'MCS'                                     => 'MCS',
    ];

    /**
     * GET admin/exams/mass-update-specialty
     */
    // ── Designation Options Admin ────────────────────────────────────────────

    public function designationsIndex()
    {
        $options = DB::table('designation_options')->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.exams.designations', ['options' => $options]);
    }

    public function designationsStore(Request $request)
    {
        $name = trim($request->input('name', ''));
        if (!$name) return back()->with('error', 'Name cannot be empty.');
        if (strlen($name) > 80) return back()->with('error', 'Name too long (max 80 chars).');

        $exists = DB::table('designation_options')->where('name', $name)->exists();
        if ($exists) return back()->with('error', '"' . $name . '" already exists.');

        $maxOrder = DB::table('designation_options')->max('sort_order') ?? 0;
        DB::table('designation_options')->insert([
            'name'       => $name,
            'sort_order' => $maxOrder + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', '"' . $name . '" added successfully.');
    }

    public function designationsDelete($id)
    {
        $opt = DB::table('designation_options')->find($id);
        if (!$opt) return back()->with('error', 'Option not found.');

        // Warn if in use (don't block, just inform)
        $inUse = DB::table('examiners')->where('examiner_designation', $opt->name)->count();
        DB::table('designation_options')->delete($id);

        $msg = '"' . $opt->name . '" deleted.';
        if ($inUse) $msg .= ' Note: ' . $inUse . ' examiner(s) still have this designation — it will still display on their profiles.';
        return back()->with('success', $msg);
    }

    public function massUpdateSpecialtyForm()
    {
        $current = DB::table('examiners')
            ->select('specialty', DB::raw('COUNT(*) as cnt'))
            ->groupBy('specialty')
            ->orderBy('specialty')
            ->get()
            ->map(function ($row) {
                $canon = self::SPECIALTY_MAP[$row->specialty] ?? null;
                return (object)[
                    'specialty' => $row->specialty,
                    'cnt'       => $row->cnt,
                    'mapped_to' => $canon,
                    'needs_fix' => $canon && $canon !== $row->specialty,
                ];
            });

        $programmes = DB::table('programmes')->orderBy('name')->pluck('name');

        return view('admin.exams.mass_update_specialty', [
            'header_title' => 'Mass Update Examiner Specialty',
            'current'      => $current,
            'programmes'   => $programmes,
        ]);
    }

    /**
     * POST admin/exams/mass-update-specialty
     */
    public function massUpdateSpecialtyProcess(Request $request)
    {
        $updated = 0;
        foreach (self::SPECIALTY_MAP as $from => $to) {
            $rows = DB::table('examiners')
                ->where('specialty', $from)
                ->update(['specialty' => $to]);
            $updated += $rows;
        }

        // Handle custom overrides submitted via form (from → to pairs)
        $overrides = $request->input('overrides', []);
        foreach ($overrides as $from => $to) {
            $to = trim($to);
            if ($to === '') continue;
            $rows = DB::table('examiners')
                ->where('specialty', urldecode($from))
                ->update(['specialty' => $to]);
            $updated += $rows;
        }

        return redirect()->route('exams.mass.specialty')
            ->with('success', "Specialty normalised — {$updated} record(s) updated.");
    }

    /**
     * POST admin/exams/manage-participation/{examiner_id}
     * Called from the Manage Participation modal on view_examiner.
     */
    public function manageParticipation(Request $request, $examiner_id)
    {
        DB::beginTransaction();
        try {
            $examiner = ExamsModel::findOrFail($examiner_id);

            $selectedYearNames = array_map('strval', $request->examination_years ?? []);

            // Update examination_years in history
            ExaminerHistory::updateOrCreate(
                ['exm_id' => $examiner->id],
                ['examination_years' => $selectedYearNames ?: null]
            );

            // Sync the full year range (2020 → lastYear) so unchecked years are cleared
            $this->syncParticipations($examiner->id, $selectedYearNames, $request->year_programme ?? [], $request->year_role ?? []);

            DB::commit();

            $from = $request->input('from', 'admin/exams/examiners');
            return redirect("admin/exams/view_examiner/{$examiner->id}?from=" . urlencode($from))
                ->with('success', 'Participation history updated successfully');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->with('error', 'Failed to update participation: ' . $e->getMessage());
        }
    }

    /**
     * Build a lightweight single-examiner object for attendance methods.
     * Avoids loading all 700+ examiners via User::getExaminers().
     */
    private function fetchExaminerForAttendance($examiner_id)
    {
        $yearId = User::getCurrentYearId();

        $examiner = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('countries', 'countries.id', '=', 'examiners.country_id')
            ->leftJoin('exams_shifts', function ($join) use ($yearId) {
                $join->on('exams_shifts.exm_id', '=', 'examiners.id')
                     ->where('exams_shifts.year_id', '=', $yearId);
            })
            ->leftJoin('exams_groups', function ($join) use ($yearId) {
                $join->on('exams_groups.exm_id', '=', 'examiners.id')
                     ->where('exams_groups.year_id', '=', $yearId);
            })
            ->leftJoin('examiners_groups', 'exams_groups.group_id', '=', 'examiners_groups.id')
            ->where('examiners.id', $examiner_id)
            ->select(
                'examiners.id as examin_id',
                'examiners.user_id',
                'users.name as examiner_name',
                'users.email',
                'examiners.specialty',
                'examiners.subspecialty',
                'examiners.country_id',
                'countries.country_name',
                'examiners.mobile',
                'examiners.curriculum_vitae',
                'examiners.passport_image',
                'examiners.examiner_id',
                'examiners_groups.id as group_id',
                'examiners_groups.group_name',
                'exams_shifts.shift as shift_num'
            )
            ->first();

        if ($examiner) {
            $examiner->shift = $examiner->shift_num !== null
                ? User::getShiftName($examiner->shift_num)
                : null;
        }

        return $examiner;
    }

    /**
     * Show attendance confirmation page after QR scan
     */
    public function showAttendanceConfirmation($examiner_id)
    {
        $examiner = $this->fetchExaminerForAttendance($examiner_id);

        if (!$examiner) {
            return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
        }

        $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        $data = [
            'header_title'      => 'Confirm Attendance Registration',
            'examiner'          => $examiner,
            'already_registered'=> $existingAttendance ? true : false,
            'registration_time' => $existingAttendance ? $existingAttendance->created_at->format('H:i:s') : null,
        ];

        return view('admin.exams.confirm_attendance', $data);
    }

    /**
     * Process attendance registration after confirmation
     */
    public function confirmAttendanceRegistration(Request $request, $examiner_id)
    {
        try {
            $examiner = $this->fetchExaminerForAttendance($examiner_id);

            if (!$examiner) {
                return redirect()->back()->with('error', 'Examiner not found');
            }

            $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existingAttendance) {
                return redirect()->back()->with(
                    'info',
                    'Attendance already recorded for today at ' . $existingAttendance->created_at->format('H:i:s')
                );
            }

            $attendance = Attendance::create([
                'user_id'          => $examiner->user_id ?? null,
                'examiner_id'      => $examiner->examin_id ?? null,
                'country_id'       => $examiner->country_id ?? null,
                'group_id'         => $examiner->group_id ?? null,
                'mobile'           => $examiner->mobile ?? null,
                'specialty'        => $examiner->specialty ?? null,
                'subspecialty'     => $examiner->subspecialty ?? null,
                'shift'            => $examiner->shift_num ?? null,
                'curriculum_vitae' => $examiner->curriculum_vitae ?? null,
                'passport_image'   => $examiner->passport_image ?? null,
            ]);

            return redirect()->back()->with(
                'success',
                'Attendance registered successfully for ' . $examiner->examiner_name . ' at ' . $attendance->created_at->format('H:i:s')
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error registering attendance: ' . $e->getMessage());
        }
    }

    /**
     * List all attendance records (admin view), with optional CSV export.
     */
    public function attendanceList(Request $request)
    {
        // null = no filter (show all); a date string = filter to that day
        $dateFilter  = $request->get('date', null);
        $shiftLabels = [1 => 'Morning', 2 => 'Morning & Afternoon', 3 => 'Afternoon'];

        $records = DB::table('attendance')
            ->join('examiners', 'examiners.id', '=', 'attendance.examiner_id')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('countries', 'countries.id', '=', 'attendance.country_id')
            ->leftJoin('examiners_groups', 'examiners_groups.id', '=', 'attendance.group_id')
            ->when($dateFilter, fn ($q) => $q->whereDate('attendance.created_at', $dateFilter))
            ->select(
                'attendance.id',
                DB::raw('DATE(attendance.created_at) as attendance_date'),
                'attendance.created_at as checked_in_at',
                'attendance.shift',
                'attendance.specialty',
                'users.name as examiner_name',
                'examiners.examiner_id as badge_id',
                'countries.country_name',
                'examiners_groups.group_name'
            )
            ->orderBy('attendance.created_at', 'desc')
            ->get();

        // CSV export
        if ($request->get('export') === '1') {
            $filename = 'attendance_' . ($dateFilter ?? 'all') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($records, $shiftLabels) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['#', 'Date', 'Name', 'Badge ID', 'Specialty', 'Country', 'Group', 'Shift', 'Check-in Time']);
                foreach ($records as $i => $rec) {
                    fputcsv($out, [
                        $i + 1,
                        $rec->attendance_date,
                        $rec->examiner_name,
                        $rec->badge_id ?? '',
                        $rec->specialty ?? '',
                        $rec->country_name ?? '',
                        $rec->group_name ?? '',
                        $shiftLabels[$rec->shift] ?? ($rec->shift ?? ''),
                        Carbon::parse($rec->checked_in_at)->format('H:i:s'),
                    ]);
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        // All distinct dates that have attendance records (for quick-jump links)
        $availableDates = DB::table('attendance')
            ->selectRaw('DATE(created_at) as date')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->pluck('date');

        $data = [
            'header_title'   => 'Examiner Attendance',
            'records'        => $records,
            'dateFilter'     => $dateFilter,
            'availableDates' => $availableDates,
            'totalRecords'   => $records->count(),
        ];

        return view('admin.exams.attendance_list', $data);
    }


    /**
     * Resolve the year filter from the request.
     * Returns [$yearId, $yearName, $allYears].
     */
    private function resolveYear(Request $request): array
    {
        $allYears = DB::table('years')->orderByDesc('id')->get(['id', 'year_name']);
        $yearId   = $request->input('year_id') ? (int)$request->input('year_id') : User::getCurrentYearId();
        $yearRow  = $allYears->firstWhere('id', $yearId);
        $yearName = $yearRow ? $yearRow->year_name : (string)date('Y');
        return [$yearId, $yearName, $allYears];
    }

    public function adminResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'MCS Results';
        $data['getResults']       = User::getAdminExamsResults($yearId);
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
        return view('admin.exams.exam_results', $data);
    }

    public function gsResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'GS Results';
        $data['getResults']       = User::getGsResults($yearId);
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
        return view('admin.exams.gs_results', $data);
    }

    // Add this helper method to get FCS results
    // Better version - properly aggregates multiple examiner results per station
    private function getFcsResults($programmeId, $tableName, $yearId = null)
    {
        $examYearId = $yearId ?? User::getCurrentYearId();

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
    public function cardiothoracicResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'FCS Cardiothoracic Results';
        $data['getResults']       = $this->getFcsResults(1, 'cardiothoracic_results', $yearId);
        $data['programmeName']    = 'Cardiothoracic Surgery';
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
        return view('admin.exams.fcs_cardiothoracic_results', $data);
    }

// Urology Results
    public function urologyResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'FCS Urology Results';
        $data['getResults']       = $this->getFcsResults(9, 'urology_results', $yearId);
        $data['programmeName']    = 'Urology';
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
        return view('admin.exams.fcs_urology_results', $data);
    }

// Paediatric Surgery Results
    public function paediatricResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'FCS Paediatric Surgery Results';
        $data['getResults']       = $this->getFcsResults(7, 'paediatric_results', $yearId);
        $data['programmeName']    = 'Paediatric Surgery';
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
        return view('admin.exams.fcs_paediatric_results', $data);
    }

// ENT Results
    public function entResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'FCS ENT Results';
        $data['getResults']       = $this->getFcsResults(5, 'ent_results', $yearId);
        $data['programmeName']    = 'ENT Surgery';
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
        return view('admin.exams.fcs_ent_results', $data);
    }

// Plastic Surgery Results
    public function plasticSurgeryResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'FCS Plastic Surgery Results';
        $data['getResults']       = $this->getFcsResults(8, 'plastic_surgery_results', $yearId);
        $data['programmeName']    = 'Plastic Surgery';
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
        return view('admin.exams.fcs_plastic_surgery_results', $data);
    }

// Neurosurgery Results
    public function neurosurgeryResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'FCS Neurosurgery Results';
        $data['getResults']       = $this->getFcsResults(3, 'neurosurgery_results', $yearId);
        $data['programmeName']    = 'Neurosurgery';
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
        return view('admin.exams.fcs_neurosurgery_results', $data);
    }

// Orthopaedics Results
    public function orthopaedicsResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'FCS Orthopaedics Results';
        $data['getResults']       = $this->getFcsResults(4, 'orthopaedic_results', $yearId);
        $data['programmeName']    = 'Orthopaedics';
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
        return view('admin.exams.fcs_orthopaedics_results', $data);
    }

// Paediatric Orthopaedics Results
    public function paediatricOrthopaedicsResults(Request $request)
    {
        [$yearId, $yearName, $allYears] = $this->resolveYear($request);
        $data['header_title']     = 'FCS Paediatric Orthopaedics Results';
        $data['getResults']       = $this->getFcsResults(6, 'paediatric_orthopaedics_results', $yearId);
        $data['programmeName']    = 'Paediatric Orthopaedics';
        $data['allYears']         = $allYears;
        $data['selectedYearId']   = $yearId;
        $data['selectedYearName'] = $yearName;
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

    /**
     * AJAX: return all results rows for a given examiner+candidate pair across all programmes.
     */
    public function examinerCandidateResults($examiner_id, $candidate_id)
    {
        $yearsMap = DB::table('years')->pluck('year_name', 'id')->toArray();

        $tables = [
            'mcs_results'                     => 'MCS',
            'gs_results'                      => 'FCS General Surgery',
            'cardiothoracic_results'          => 'FCS Cardiothoracic',
            'urology_results'                 => 'FCS Urology',
            'paediatric_results'              => 'FCS Paediatric Surgery',
            'ent_results'                     => 'FCS ENT',
            'plastic_surgery_results'         => 'FCS Plastic Surgery',
            'neurosurgery_results'            => 'FCS Neurosurgery',
            'orthopaedic_results'             => 'FCS Orthopaedic Surgery',
            'paediatric_orthopaedics_results' => 'FCS Paediatric Orthopaedics',
        ];

        // Candidate name for the modal title
        $candidateInfo = DB::table('candidates')
            ->where('candidates.id', $candidate_id)
            ->select(
                'candidates.candidate_id as candidate_no',
                DB::raw("TRIM(CONCAT(COALESCE(firstname,''),' ',COALESCE(middlename,''),' ',COALESCE(lastname,''))) as name")
            )->first();

        $rows = [];
        foreach ($tables as $table => $programme) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) continue;
            $cols = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            if (!in_array('examiner_id', $cols) || !in_array('candidate_id', $cols)) continue;

            $hasFormat  = in_array('exam_format', $cols);
            $hasStation = in_array('station_id', $cols);
            $hasTotal   = in_array('total', $cols);
            $hasOverall = in_array('overall', $cols);
            $hasRemarks = in_array('remarks', $cols);
            $hasQmark   = in_array('question_mark', $cols);
            $yearCol    = in_array('exam_year', $cols) ? "exam_year" : null;

            $select = [
                DB::raw("'$programme' as programme"),
                DB::raw($hasStation ? "$table.station_id" : "NULL as station_id"),
                DB::raw($hasFormat  ? "$table.exam_format" : "NULL as exam_format"),
                DB::raw($hasTotal   ? "$table.total" : "NULL as total"),
                DB::raw($hasOverall ? "$table.overall" : "NULL as overall"),
                DB::raw($hasRemarks ? "$table.remarks" : "NULL as remarks"),
                DB::raw($hasQmark   ? "$table.question_mark" : "NULL as question_mark"),
                DB::raw($yearCol    ? "$table.$yearCol as exam_year_id" : "NULL as exam_year_id"),
            ];

            $results = DB::table($table)
                ->select($select)
                ->where('examiner_id', $examiner_id)
                ->where('candidate_id', $candidate_id)
                ->get();

            foreach ($results as $r) {
                $yr = $r->exam_year_id;
                $rows[] = [
                    'programme'    => $r->programme,
                    'station_id'   => $r->station_id,
                    'exam_format'  => $r->exam_format,
                    'total'        => $r->total,
                    'overall'      => $r->overall,
                    'remarks'      => $r->remarks,
                    'question_mark'=> $r->question_mark ? json_decode($r->question_mark, true) : null,
                    'exam_year'    => $yr && isset($yearsMap[$yr]) ? $yearsMap[$yr] : ($yr ?? '—'),
                ];
            }
        }

        // Sort: programme → station
        usort($rows, fn($a, $b) => $a['programme'] <=> $b['programme'] ?: ($a['station_id'] <=> $b['station_id']));

        return response()->json([
            'candidate' => $candidateInfo,
            'results'   => $rows,
        ]);
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

        // ── Participation history (same logic as admin viewExaminer) ──────────
        $examinerId = $examiner->examin_id;
        $history    = DB::table('examiners_history')->where('exm_id', $examinerId)->first();

        $rawYears     = $history->examination_years ?? null;
        $decodedYears = json_decode($rawYears, true);
        if (is_string($decodedYears)) { $decodedYears = json_decode($decodedYears, true); }
        $examinedYears = is_array($decodedYears) ? $decodedYears : [];

        $hasParticipations = \Illuminate\Support\Facades\Schema::hasTable('examiner_participations');
        $allEP = [];
        if ($hasParticipations) {
            $epRows = DB::table('examiner_participations')
                ->join('years', 'years.id', '=', 'examiner_participations.year_id')
                ->where('examiner_participations.exm_id', $examinerId)
                ->whereNotNull('examiner_participations.specialty')
                ->select('years.year_name', 'examiner_participations.specialty', 'examiner_participations.role')
                ->get();
            foreach ($epRows as $row) {
                $allEP[(string)$row->year_name][$row->specialty] = $row->role ?: null;
            }
        }

        $defaultRole  = ($examiner->role_id == 1) ? 'Examiner' : 'Observer';
        $yearProgrammes = [];
        $yearRoles      = [];

        foreach ($examinedYears as $yrName) {
            $yearRow = DB::table('years')->where('year_name', (string)$yrName)->first();
            if (!$yearRow) continue;
            $yid = $yearRow->id;

            $programmes = [];
            $roles      = [];

            if (!empty($allEP[(string)$yrName])) {
                foreach ($allEP[(string)$yrName] as $spec => $role) {
                    $programmes[] = $spec;
                    $roles[$spec] = $role ?? $defaultRole;
                }
            }

            if (!in_array('MCS', $programmes) &&
                DB::table('mcs_results')->where('examiner_id', $examinerId)->where('exam_year', $yid)->exists()) {
                $programmes[] = 'MCS';
                $roles['MCS'] = $defaultRole;
            }

            $hasFCS = !empty(array_filter($programmes, fn($p) => stripos($p, 'FCS') !== false));
            if (!$hasFCS &&
                DB::table('gs_results')->where('examiner_id', $examinerId)->where('exam_year', $yid)->exists()) {
                $programmes[] = 'FCS General Surgery';
                $roles['FCS General Surgery'] = $defaultRole;
            }

            $yearProgrammes[(string)$yrName] = array_unique($programmes);
            $yearRoles[(string)$yrName]      = $roles;
        }

        $data = [
            'header_title'   => 'Profile Settings',
            'examiner'       => $examiner,
            'getCountry'     => Country::getCountry(),
            'groups'         => DB::table('examiners_groups')->select('id', 'group_name')->get(),
            'qrCode'         => $qrCode,
            'exYears'        => $examinedYears,
            'yearProgrammes' => $yearProgrammes,
            'yearRoles'      => $yearRoles,
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
        $examiner = $this->fetchExaminerForAttendance($examiner_id);

        if (!$examiner) {
            return redirect('examiner/dashboard')->with('error', 'Examiner not found');
        }

        $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        $data = [
            'header_title'      => 'Confirm Attendance Registration',
            'examiner'          => $examiner,
            'already_registered'=> $existingAttendance ? true : false,
            'registration_time' => $existingAttendance ? $existingAttendance->created_at->format('H:i:s') : null,
        ];

        return view('examiner.confirm_attendance', $data);
    }

    /**
     * Process attendance registration after confirmation (Examiner-specific version)
     */
    public function confirmExaminerAttendanceRegistration(Request $request, $examiner_id)
    {
        try {
            $examiner = $this->fetchExaminerForAttendance($examiner_id);

            if (!$examiner) {
                return redirect()->back()->with('error', 'Examiner not found');
            }

            $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existingAttendance) {
                return redirect()->back()->with(
                    'info',
                    'Attendance already recorded for today at ' . $existingAttendance->created_at->format('H:i:s')
                );
            }

            $attendance = Attendance::create([
                'user_id'          => $examiner->user_id ?? null,
                'examiner_id'      => $examiner->examin_id ?? null,
                'country_id'       => $examiner->country_id ?? null,
                'group_id'         => $examiner->group_id ?? null,
                'mobile'           => $examiner->mobile ?? null,
                'specialty'        => $examiner->specialty ?? null,
                'subspecialty'     => $examiner->subspecialty ?? null,
                'shift'            => $examiner->shift_num ?? null,
                'curriculum_vitae' => $examiner->curriculum_vitae ?? null,
                'passport_image'   => $examiner->passport_image ?? null,
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
            ['exam_availability' => $availability, 'availability_year_id' => User::getCurrentYearId()]
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

        // Dynamic exam years: all completed years up to (but not including) current year
        $data['examYears'] = DB::table('years')
            ->where('id', '<', User::getCurrentYearId())
            ->orderByDesc('id')
            ->pluck('year_name');

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

        // Maximum selectable year is always the last completed exam year
        $lastYearName = DB::table('years')
            ->where('id', User::getCurrentYearId() - 1)
            ->value('year_name') ?? (date('Y') - 1);

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
            'examination_years.*' => 'integer|min:2020|max:' . $lastYearName,
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

                $historyData['exam_availability']    = $availability;
                $historyData['availability_year_id'] = User::getCurrentYearId();
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
                $historyData['examination_years'] = $validated['examination_years'];
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
