<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FellowsModel;
use App\Models\FellowLabel;
use App\Models\Country;
use App\Models\UserRole;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FellowshipImport;

class FellowsController extends Controller
{
    public function list()
    {
        $data['header_title'] = 'Fellows';
        $data['getFellows']        = User::getFellows();
        $data['filterCountries']   = DB::table('fellows as f')
            ->join('countries as c', 'c.id', '=', 'f.country_id')
            ->select('c.country_name')->groupBy('c.country_name')->orderBy('c.country_name')->pluck('c.country_name');
        $data['filterTypes']       = DB::table('fellows as f')
            ->join('categories as c', 'c.id', '=', 'f.category_id')
            ->select('c.category_name')->groupBy('c.category_name')->orderBy('c.category_name')->pluck('c.category_name');
        $data['filterProgrammes']  = DB::table('fellows as f')
            ->join('programmes as p', 'p.id', '=', 'f.programme_id')
            ->select('p.name')->groupBy('p.name')->orderBy('p.name')->pluck('p.name');
        $data['filterYears']       = DB::table('fellows')
            ->whereRaw("fellowship_year REGEXP '^[0-9]{4}$'")->select('fellowship_year')
            ->groupBy('fellowship_year')->orderByDesc('fellowship_year')->pluck('fellowship_year');

        // Every alumni fellow is one row regardless of how many FCS specialties
        // they hold — "unique" counts rows, "all" also counts each extra
        // specialty (second_fcs_specialty/third_fcs_specialty) as its own entry,
        // matching how the source alumni spreadsheet's pivot table counts them.
        $data['uniqueAlumniCount'] = DB::table('fellows')->where('category_id', 5)->where('is_alumni', 1)->count();
        $data['allAlumniCount']    = DB::table('fellows')->where('category_id', 5)->where('is_alumni', 1)
            ->selectRaw("SUM(1 + (second_fcs_specialty IS NOT NULL AND second_fcs_specialty != '') + (third_fcs_specialty IS NOT NULL AND third_fcs_specialty != '')) as total")
            ->value('total');

        // Extra virtual rows for the "All Alumni" view — one per additional
        // FCS specialty a fellow holds, added into the table client-side only
        // when that view is selected (so the default row count never changes).
        $extraAlumniRows = [];
        $multiSpecFellows = DB::table('fellows as f')
            ->leftJoin('users as u', 'u.id', '=', 'f.user_id')
            ->leftJoin('countries as c', 'c.id', '=', 'f.country_id')
            ->where('f.category_id', 5)->where('f.is_alumni', 1)
            ->where(function ($q) {
                $q->whereNotNull('f.second_fcs_specialty')->where('f.second_fcs_specialty', '!=', '')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('f.third_fcs_specialty')->where('f.third_fcs_specialty', '!=', '');
                  });
            })
            ->select('f.id as fellow_id', 'u.name as fellow_name', 'f.personal_email', 'f.country_id',
                     'c.country_name', 'f.second_fcs_specialty', 'f.second_fcs_year',
                     'f.third_fcs_specialty', 'f.third_fcs_year')
            ->get();
        foreach ($multiSpecFellows as $f) {
            if (!empty($f->second_fcs_specialty)) {
                $extraAlumniRows[] = [
                    'fellow_id' => $f->fellow_id, 'name' => $f->fellow_name, 'email' => $f->personal_email,
                    'country_id' => $f->country_id, 'country_name' => $f->country_name,
                    'specialty' => $f->second_fcs_specialty, 'year' => $f->second_fcs_year,
                ];
            }
            if (!empty($f->third_fcs_specialty)) {
                $extraAlumniRows[] = [
                    'fellow_id' => $f->fellow_id, 'name' => $f->fellow_name, 'email' => $f->personal_email,
                    'country_id' => $f->country_id, 'country_name' => $f->country_name,
                    'specialty' => $f->third_fcs_specialty, 'year' => $f->third_fcs_year,
                ];
            }
        }
        $data['extraAlumniRows'] = $extraAlumniRows;

        return view('admin.associates.fellows.list', $data);
    }

    // ── Analytics / Visual Reports ────────────────────────────────────────────
    public function reports()
    {
        $data['header_title']     = 'Fellows Analytics';
        $data['filterCountries']  = DB::table('fellows as f')
            ->join('countries as c', 'c.id', '=', 'f.country_id')
            ->select('c.id', 'c.country_name')->groupBy('c.id', 'c.country_name')->orderBy('c.country_name')->get();
        $data['filterTypes']      = DB::table('fellows as f')
            ->join('categories as c', 'c.id', '=', 'f.category_id')
            ->select('c.id', 'c.category_name')->groupBy('c.id', 'c.category_name')->orderBy('c.category_name')->get();
        $data['filterYears']      = DB::table('fellows')
            ->whereRaw("fellowship_year REGEXP '^[0-9]{4}$'")->select('fellowship_year')
            ->groupBy('fellowship_year')->orderByDesc('fellowship_year')->pluck('fellowship_year');
        return view('admin.associates.fellows.reports', $data);
    }

    public function reportsData()
    {
        $countryId  = request('country_id');
        $categoryId = request('category_id');
        $year       = request('year');
        $gender     = request('gender');
        $isAlumni   = request('is_alumni');

        // Helper: apply filters to a query with a given table prefix
        $applyF = function ($q, $pfx = 'fellows') use ($countryId, $categoryId, $year, $gender, $isAlumni) {
            if ($countryId)  $q->where("{$pfx}.country_id",  $countryId);
            if ($categoryId) $q->where("{$pfx}.category_id", $categoryId);
            if ($year !== null && $year !== '') $q->where("{$pfx}.fellowship_year", $year);
            if ($gender)     $q->where("{$pfx}.gender",       $gender);
            if ($isAlumni !== null && $isAlumni !== '') $q->where("{$pfx}.is_alumni", (int) $isAlumni);
        };

        // KPIs
        $q = DB::table('fellows'); $applyF($q);
        $total  = (clone $q)->count();
        $active = (clone $q)->where('status', 'Active')->count();
        $male   = (clone $q)->where('gender', 'Male')->count();
        $female = (clone $q)->where('gender', 'Female')->count();

        // Fellows by country (top 15)
        $byCountry = tap(DB::table('fellows as f')->join('countries as c', 'c.id', '=', 'f.country_id'), fn($q) => $applyF($q, 'f'))
            ->select('c.country_name as label', DB::raw('COUNT(*) as value'))
            ->groupBy('c.country_name')->orderByDesc('value')->limit(15)->get();

        // Fellowship type breakdown
        $byType = tap(DB::table('fellows as f')->join('categories as c', 'c.id', '=', 'f.category_id'), fn($q) => $applyF($q, 'f'))
            ->select('c.category_name as label', DB::raw('COUNT(*) as value'))
            ->groupBy('c.category_name')->orderByDesc('value')->get();

        // Gender breakdown
        $byGender = tap(DB::table('fellows'), fn($q) => $applyF($q))
            ->select(DB::raw("COALESCE(NULLIF(gender,''),'Unknown') as label"), DB::raw('COUNT(*) as value'))
            ->groupBy('label')->get();

        // Fellowship year trend (2010–present)
        $byYear = tap(DB::table('fellows'), fn($q) => $applyF($q))
            ->select('fellowship_year as label', DB::raw('COUNT(*) as value'))
            ->whereRaw("fellowship_year REGEXP '^[0-9]{4}$'")
            ->whereRaw("CAST(fellowship_year AS UNSIGNED) >= 2010")
            ->groupBy('fellowship_year')->orderBy('fellowship_year')->get();

        // Top specialties (top 10)
        $rawSpec = tap(DB::table('fellows'), fn($q) => $applyF($q))
            ->select('current_specialty as label', DB::raw('COUNT(*) as value'))
            ->whereNotNull('current_specialty')->where('current_specialty', '!=', '')
            ->groupBy('current_specialty')->orderByDesc('value')->limit(10)->get();

        // Annual subscriptions status by year (2015–current)
        $subYears = range(2015, (int) date('Y'));
        $subData  = [];
        foreach ($subYears as $yr) {
            $row = DB::table('fellow_subscriptions')
                ->where('year', $yr)
                ->select('status', DB::raw('COUNT(*) as cnt'))
                ->groupBy('status')
                ->get()
                ->keyBy('status');
            $subData[] = [
                'year'    => $yr,
                'Paid'    => $row->get('Paid')?->cnt   ?? 0,
                'Unpaid'  => $row->get('Unpaid')?->cnt ?? 0,
                'Waived'  => $row->get('Waived')?->cnt ?? 0,
            ];
        }

        // Exam pass/fail by year & part (last 5 years)
        $examStats = DB::table('fellow_exam_results')
            ->select('year', 'part', 'result', DB::raw('COUNT(*) as cnt'))
            ->where('year', '>=', (int) date('Y') - 4)
            ->whereNotNull('result')
            ->where('result', '!=', '')
            ->groupBy('year', 'part', 'result')
            ->orderBy('year')->orderBy('part')
            ->get();

        // Country representative table (top 20)
        $countryTable = tap(
            DB::table('fellows as f')
                ->join('countries as c', 'c.id', '=', 'f.country_id')
                ->leftJoin('categories as cat', 'cat.id', '=', 'f.category_id'),
            fn($q) => $applyF($q, 'f')
        )
            ->select(
                'c.country_name',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN f.gender='Male' THEN 1 ELSE 0 END) as male"),
                DB::raw("SUM(CASE WHEN f.gender='Female' THEN 1 ELSE 0 END) as female"),
                DB::raw("SUM(CASE WHEN f.status='Active' THEN 1 ELSE 0 END) as active")
            )
            ->groupBy('c.country_name')->orderByDesc('total')->limit(20)->get();

        return response()->json([
            'kpi'          => compact('total', 'active', 'male', 'female'),
            'byCountry'    => $byCountry,
            'byType'       => $byType,
            'byGender'     => $byGender,
            'byYear'       => $byYear,
            'bySpecialty'  => $rawSpec,
            'subscriptions'=> $subData,
            'examStats'    => $examStats,
            'countryTable' => $countryTable,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate')
          ->header('Pragma', 'no-cache');
    }

    public function view($id)
    {
        $fellow = User::getFellows()->firstWhere('fellow_id', $id);

        // Fallback: direct DB lookup in case the fellow lacks a user_roles entry
        // (e.g. dual examiner-fellow whose role_type=7 row is missing on this environment)
        if (!$fellow) {
            $fellow = DB::table('fellows')
                ->join('users', 'users.id', '=', 'fellows.user_id')
                ->leftJoin('programmes', 'fellows.programme_id', '=', 'programmes.id')
                ->leftJoin('categories', 'fellows.category_id', '=', 'categories.id')
                ->leftJoin('countries', 'fellows.country_id', '=', 'countries.id')
                ->where('fellows.id', $id)
                ->select(
                    'users.id as user_id',
                    'users.name as fellow_name',
                    'users.email as email',
                    'fellows.id as fellow_id',
                    'fellows.user_id as f_id',
                    'fellows.personal_email as personal_email',
                    'fellows.*',
                    'programmes.name as programme_name',
                    'countries.country_name as country_name',
                    'categories.category_name as fellowship_type'
                )
                ->first();
        }

        if (!$fellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }

        // Load subscriptions
        $subscriptions = \DB::table('fellow_subscriptions')
            ->where('fellow_id', $fellow->fellow_id)
            ->orderBy('year', 'desc')
            ->get();

        // Load FCS exam results (from internal exam tables)
        $fellowResults = \DB::table('fellow_exam_results')
            ->where('fellow_id', $fellow->fellow_id)
            ->orderByDesc('year')
            ->orderBy('part')
            ->get();

        // Load Capsule CRM exam results. Prefer the reliable fellow_id link (set
        // when enriching 2020-2025 results from the examination officer's
        // spreadsheet, matched via PEN/candidate_number), then fall back to the
        // legacy capsule_id / contact_name match for older, unlinked records.
        $fellowEmail = strtolower(trim($fellow->email ?? ''));
        $capsuleId = $fellowEmail
            ? \DB::table('capsule_contacts')->whereRaw('LOWER(email) = ?', [$fellowEmail])->value('capsule_id')
            : null;
        $fullName = trim(($fellow->firstname ?? '') . ' ' . ($fellow->lastname ?? ''));

        $capsuleExamResults = \DB::table('capsule_exam_results')
            ->where('fellow_id', $fellow->fellow_id)
            ->when($capsuleId, fn($q) => $q->orWhere('capsule_id', $capsuleId))
            ->when($fullName, fn($q) => $q->orWhereRaw('LOWER(contact_name) = LOWER(?)', [$fullName]))
            ->orderByDesc('exam_year')
            ->orderBy('specialty')
            ->get()
            ->unique('id')
            ->values();

        // Load labels
        $fellowRecord = FellowsModel::find($fellow->fellow_id);
        $allLabels = FellowLabel::where('is_active', true)->orderBy('name')->get();
        $assignedLabels = $fellowRecord ? $fellowRecord->labels : collect();
        $currentLabelIds = $assignedLabels->pluck('id')->toArray();

        $header_title = "View Fellow";
        return view('admin.associates.fellows.view', compact(
            'fellow', 'header_title', 'subscriptions',
            'allLabels', 'assignedLabels', 'currentLabelIds', 'fellowResults',
            'capsuleExamResults'
        ));
    }

    public function add()
    {
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Fellow";
        return view('admin.associates.fellows.add', $data);
    }

    public function insert(Request $request)
    {
        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");

        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $userType = 7;

        $user = User::create([
            'name'      => $fullName,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'user_type' => $userType
        ]);

        UserRole::create([
            'user_id'   => $user->id,
            'role_type' => $userType,
            'is_active' => 1
        ]);

        $fellow = FellowsModel::create([
            'user_id'                       => $user->id,
            'firstname'                     => $request->firstname,
            'middlename'                    => $request->middlename,
            'lastname'                      => $request->lastname,
            'personal_email'                => $request->personal_email,
            'second_email'                  => $request->second_email,
            'gender'                        => $request->gender,
            'status'                        => $request->status,
            'address'                       => $request->address,
            'country_id'                    => $request->country_id,
            'programme_id'                  => $request->programme_id,
            'category_id'                   => $request->category_id ?: null,
            'organization'                  => $request->organization,
            'profile_image'                 => $profileImagePath,
            'admission_year'                => $request->admission_year,
            'fellowship_year'               => $request->fellowship_year,
            'current_specialty'             => $request->current_specialty,
            'phone_number'                  => $request->phone_number,
            'is_promoted'                   => $request->is_promoted ?? '0',
            // Extended fields
            'candidate_number'              => $request->candidate_number,
            'supervised_by'                 => $request->supervised_by,
            'registered_by'                 => $request->registered_by,
            'secretariat_registration_date' => $request->secretariat_registration_date ?: null,
            'prog_entry_fee_year'           => $request->prog_entry_fee_year,
            'prog_entry_mode_payment'       => $request->prog_entry_mode_payment,
            'exam_fee_year'                 => $request->exam_fee_year,
            'exam_fee_date_paid'            => $request->exam_fee_date_paid ?: null,
            'exam_fee_mode_payment'         => $request->exam_fee_mode_payment,
            'exam_fee_amount_paid'          => $request->exam_fee_amount_paid,
            'exam_fee_payment_verified'     => $request->exam_fee_payment_verified ?? 0,
            'sponsored_by'                  => $request->sponsored_by,
            'mcs_qualification_year'        => $request->mcs_qualification_year,
            'country_mcs_training'          => $request->country_mcs_training,
            'exam_year_upcoming'            => $request->exam_year_upcoming,
            'exam_year_previous'            => $request->exam_year_previous,
            'cosecsa_region'                => $request->cosecsa_region,
        ]);

        return redirect('admin/associates/fellows/subscriptions/' . $fellow->id)
            ->with('success', 'Fellow added successfully! You can now add annual subscription records.');
    }

    public function import()
    {
        $data['header_title'] = "Import Fellows";
        return view('admin.associates.fellows.import_fellows', $data);
    }

    public function downloadTemplate()
    {
        $headers = [
            'firstname','middlename','lastname','email','password','gender','status',
            'candidate_number','category_id','programme_id','country_id','cosecsa_region',
            'phone_number','personal_email','second_email','address','organization',
            'current_specialty','admission_year','mcs_qualification_year','fellowship_year',
            'is_promoted','supervised_by','registered_by','secretariat_registration_date',
            'sponsored_by','prog_entry_fee_year','prog_entry_mode_payment',
            'exam_fee_year','exam_fee_date_paid','exam_fee_amount_paid','exam_fee_mode_payment',
            'exam_fee_payment_verified','country_mcs_training','exam_year_upcoming',
            'exam_year_previous','profile_image'
        ];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            // Sample row
            fputcsv($file, [
                'Lucy','Anne','Kaomba','l.kaomba@hospital.mw','Fellow@2024',
                'Female','Active','MW/2015/04','5','2','1','Eastern Africa',
                '+265999123456','lucy@gmail.com','','P.O. Box 100, Blantyre','Queen Elizabeth Central Hospital',
                'General Surgery','2015','2016','2018',
                '1','Dr W. Mulwafu','Secretariat','2015-01-15',
                'NORHED','2015','Bank Transfer',
                '2016','2016-03-10','500.00','Bank Transfer',
                '1','Malawi','2026','2025',''
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fellows_import_template.csv"',
        ]);
    }

    public function importFellows(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        Excel::import(new FellowshipImport, $request->file('file'));

        return redirect('admin/associates/fellows/list')->with('success', 'Fellows imported successfully');
    }

    public function edit($id)
    {
        $fellow = User::getFellows()->firstWhere('fellow_id', $id);
        if (!$fellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Fellow";
        $data['fellow'] = $fellow;
        return view('admin.associates.fellows.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $fellow = FellowsModel::find($id);
        if (!$fellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }

        $user = User::find($fellow->user_id);

        $profileImagePath = $fellow->profile_image;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");
        $user->name  = $fullName;
        $user->email = $request->email;
        if (!empty($request->password)) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        UserRole::firstOrCreate(
            ['user_id' => $user->id, 'role_type' => 7],
            ['is_active' => 1]
        );

        $fellow->update([
            'firstname'                     => $request->firstname,
            'middlename'                    => $request->middlename,
            'lastname'                      => $request->lastname,
            'personal_email'                => $request->personal_email,
            'second_email'                  => $request->second_email,
            'gender'                        => $request->gender,
            'status'                        => $request->status,
            'address'                       => $request->address,
            'country_id'                    => $request->country_id,
            'programme_id'                  => $request->programme_id,
            'category_id'                   => $request->category_id ?: null,
            'organization'                  => $request->organization,
            'profile_image'                 => $profileImagePath,
            'admission_year'                => $request->admission_year,
            'fellowship_year'               => $request->fellowship_year,
            'current_specialty'             => $request->current_specialty,
            'phone_number'                  => $request->phone_number,
            'is_promoted'                   => $request->is_promoted ?? '0',
            // Extended fields
            'candidate_number'              => $request->candidate_number,
            'supervised_by'                 => $request->supervised_by,
            'registered_by'                 => $request->registered_by,
            'secretariat_registration_date' => $request->secretariat_registration_date ?: null,
            'prog_entry_fee_year'           => $request->prog_entry_fee_year,
            'prog_entry_mode_payment'       => $request->prog_entry_mode_payment,
            'exam_fee_year'                 => $request->exam_fee_year,
            'exam_fee_date_paid'            => $request->exam_fee_date_paid ?: null,
            'exam_fee_mode_payment'         => $request->exam_fee_mode_payment,
            'exam_fee_amount_paid'          => $request->exam_fee_amount_paid,
            'exam_fee_payment_verified'     => $request->exam_fee_payment_verified ?? 0,
            'sponsored_by'                  => $request->sponsored_by,
            'mcs_qualification_year'        => $request->mcs_qualification_year,
            'country_mcs_training'          => $request->country_mcs_training,
            'exam_year_upcoming'            => $request->exam_year_upcoming,
            'exam_year_previous'            => $request->exam_year_previous,
            'cosecsa_region'                => $request->cosecsa_region,
        ]);

        return redirect('admin/associates/fellows/view/' . $id)->with('success', 'Fellow updated successfully');
    }

    public function updateLabels(Request $request, $id)
    {
        $fellow = FellowsModel::find($id);
        if (!$fellow) {
            return redirect()->back()->with('error', 'Fellow not found');
        }

        $labelIds = $request->labels ?? [];
        $fellow->labels()->sync($labelIds);

        return redirect('admin/associates/fellows/view/' . $id . '#tab-personal')
            ->with('success', 'Labels updated successfully');
    }

    // ── Subscriptions ──────────────────────────────────────────────────

    public function subscriptions($id)
    {
        $fellow = User::getFellows()->firstWhere('fellow_id', $id);
        if (!$fellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }

        $subscriptions = \DB::table('fellow_subscriptions')
            ->where('fellow_id', $id)
            ->orderBy('year', 'desc')
            ->get();

        $header_title = "Subscriptions — " . trim(($fellow->firstname ?? '') . ' ' . ($fellow->lastname ?? ''));

        return view('admin.associates.fellows.subscriptions',
            compact('fellow', 'subscriptions', 'header_title'));
    }

    public function storeSubscription(Request $request, $id)
    {
        $request->validate([
            'year'       => 'required|digits:4|integer|min:1990|max:2099',
            'status'     => 'required|in:Paid,Unpaid,Partial,Waived',
            'amount_due' => 'required|numeric|min:0',
        ]);

        // Prevent duplicate year for same fellow
        $exists = \DB::table('fellow_subscriptions')
            ->where('fellow_id', $id)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'A subscription record for ' . $request->year . ' already exists for this fellow.');
        }

        \DB::table('fellow_subscriptions')->insert([
            'fellow_id'       => $id,
            'year'            => $request->year,
            'status'          => $request->status,
            'amount_due'      => $request->amount_due ?? 0,
            'amount_paid'     => $request->amount_paid ?? 0,
            'date_paid'       => $request->date_paid ?: null,
            'mode_of_payment' => $request->mode_of_payment ?: null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Subscription record for ' . $request->year . ' added successfully.');
    }

    public function updateSubscription(Request $request, $sub_id)
    {
        $sub = \DB::table('fellow_subscriptions')->where('id', $sub_id)->first();
        if (!$sub) {
            return redirect()->back()->with('error', 'Subscription record not found.');
        }

        // Check for year conflict (exclude current record)
        $conflict = \DB::table('fellow_subscriptions')
            ->where('fellow_id', $sub->fellow_id)
            ->where('year', $request->year)
            ->where('id', '!=', $sub_id)
            ->exists();

        if ($conflict) {
            return redirect()->back()
                ->with('error', 'A subscription record for ' . $request->year . ' already exists.');
        }

        \DB::table('fellow_subscriptions')->where('id', $sub_id)->update([
            'year'            => $request->year,
            'status'          => $request->status,
            'amount_due'      => $request->amount_due ?? 0,
            'amount_paid'     => $request->amount_paid ?? 0,
            'date_paid'       => $request->date_paid ?: null,
            'mode_of_payment' => $request->mode_of_payment ?: null,
            'updated_at'      => now(),
        ]);

        return redirect('admin/associates/fellows/subscriptions/' . $sub->fellow_id)
            ->with('success', 'Subscription for ' . $request->year . ' updated successfully.');
    }

    public function deleteSubscription($sub_id)
    {
        $sub = \DB::table('fellow_subscriptions')->where('id', $sub_id)->first();
        if (!$sub) {
            return redirect()->back()->with('error', 'Subscription record not found.');
        }

        $fellow_id = $sub->fellow_id;
        $year      = $sub->year;
        \DB::table('fellow_subscriptions')->where('id', $sub_id)->delete();

        return redirect('admin/associates/fellows/subscriptions/' . $fellow_id)
            ->with('success', 'Subscription record for ' . $year . ' deleted.');
    }

    // ───────────────────────────────────────────────────────────────────

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect('admin/associates/fellows/list')->with('error', 'User not found');
        }

        $fellow = FellowsModel::where('user_id', $user->id)->first();
        if (!$fellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }

        $isFellow = \DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_type', 7)
            ->where('is_active', 1)
            ->exists();
        if (!$isFellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'User is not a fellow');
        }

        \DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_type', 7)
            ->update(['is_active' => 0, 'updated_at' => now()]);

        return redirect('admin/associates/fellows/list')->with('success', 'Fellow successfully Deleted');
    }
}
