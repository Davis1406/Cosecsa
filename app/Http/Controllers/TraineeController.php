<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Trainee;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Models\Country;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TraineesImport;
use App\Imports\TraineesApplicationImport;
use App\Imports\TraineesExcelUpdateImport;
use App\Models\Candidates;

class TraineeController extends Controller
{
    public function list()
    {
        $data['getRecord']        = User::getTrainee();
        $data['header_title']     = 'Trainees List';
        $data['filterCountries']  = DB::table('trainees as t')
            ->join('countries as co', 'co.id', '=', 't.country_id')
            ->select('co.country_name')->groupBy('co.country_name')->orderBy('co.country_name')
            ->pluck('co.country_name');
        $data['filterProgrammes'] = DB::table('trainees as t')
            ->join('programmes as p', 'p.id', '=', 't.programme_id')
            ->select('p.name')->groupBy('p.name')->orderBy('p.name')
            ->pluck('p.name');
        $data['filterYears']          = DB::table('trainees')
            ->whereNotNull('exam_year')->select('exam_year')->groupBy('exam_year')
            ->orderByDesc('exam_year')->pluck('exam_year');
        $data['filterAdmissionYears'] = DB::table('trainees')
            ->whereNotNull('admission_year')->where('admission_year', '!=', '')
            ->select('admission_year')->groupBy('admission_year')
            ->orderByDesc('admission_year')->pluck('admission_year');
        $data['filterStatuses']   = DB::table('trainees')
            ->whereNotNull('status')->where('status', '!=', '')
            ->select('status')->groupBy('status')->orderBy('status')->pluck('status');
        $data['filterHospitals']  = DB::table('trainees as t')
            ->join('hospitals as h', 'h.id', '=', 't.hospital_id')
            ->whereNotNull('h.name')->where('h.name', '!=', '')
            ->select('h.name')->groupBy('h.name')->orderBy('h.name')
            ->pluck('h.name');
        return view('admin.associates.trainees.trainees', $data);
    }
    public function view($id)
    {
        $trainee = User::getTrainee()->firstWhere('trainee_id', $id);
        if (!$trainee) {
            return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
        }

        $linkedCandidate = User::getCandidates()->firstWhere('user_id', $trainee->user_id)
            ?? \App\Models\Candidates::where('user_id', $trainee->user_id)->first();

        // ── Fee history across every programme this person has applied for,
        //    not just this trainee row's own programme — a trainee who's
        //    moved from MCS to FCS (or applied for multiple specialties)
        //    stays a single profile, with all their entry/exam fees listed
        //    here rather than spawning a second trainee record. ──
        $ownProgrammeName = DB::table('programmes')->where('id', $trainee->programme_id)->value('name');
        $traineeEmail = strtolower(trim($trainee->personal_email ?? $trainee->email ?? ''));

        $entryFees = collect();
        if ($traineeEmail) {
            $entryFees = DB::table('salesforce_applications')
                ->whereRaw('LOWER(applicant_email) = ?', [$traineeEmail])
                ->whereNotNull('entry_invoice_number')
                ->select(
                    'programme_name',
                    'entry_invoice_number as invoice_number',
                    'entry_invoice_amount as invoice_amount',
                    'entry_invoice_status as invoice_status',
                    'entry_payment_amount as amount_paid',
                    'entry_payment_date as payment_date',
                    'entry_payment_method as mode_of_payment'
                )
                ->get();
        }
        // Include this trainee's own stored entry-fee data if its programme
        // isn't already covered by a Salesforce application record.
        if (! $entryFees->contains(fn ($e) => $e->programme_name === $ownProgrammeName)
            && ($trainee->invoice_amount || $trainee->amount_paid)) {
            $entryFees->push((object) [
                'programme_name'  => $ownProgrammeName,
                'invoice_number'  => $trainee->invoice_number,
                'invoice_amount'  => $trainee->invoice_amount,
                'invoice_status'  => $trainee->invoice_status,
                'amount_paid'     => $trainee->amount_paid,
                'payment_date'    => $trainee->payment_date,
                'mode_of_payment' => $trainee->mode_of_payment,
            ]);
        }
        $entryFees = $entryFees->sortBy(fn ($e) => $e->programme_name === $ownProgrammeName ? 0 : 1)->values();

        $examFees = collect();
        if ($traineeEmail) {
            $examFees = DB::table('candidates as c')
                ->leftJoin('programmes as p', 'p.id', '=', 'c.programme_id')
                ->whereRaw('LOWER(c.personal_email) = ?', [$traineeEmail])
                ->where(fn ($w) => $w->where('c.amount_paid', '>', 0)->orWhere('c.fee_paid', 'Yes')->orWhereNotNull('c.invoice_number'))
                ->select(
                    'p.name as programme_name',
                    'c.invoice_number', 'c.invoice_amount', 'c.invoice_status',
                    'c.amount_paid', 'c.payment_date', 'c.mode_of_payment'
                )
                ->get()
                ->sortBy(fn ($e) => $e->programme_name === $ownProgrammeName ? 0 : 1)
                ->values();
        }

        // Data for inline tag editors
        $programmes = DB::table('programmes')->orderBy('name')->get(['id', 'name']);
        $countries  = DB::table('countries')->orderBy('country_name')->get(['id', 'country_name']);
        $examYears  = DB::table('years')->orderByDesc('year_name')->pluck('year_name');

        // First admission year — always derived from the PEN (CC/YYYY/NN), never overrideable
        $penParts          = explode('/', $trainee->entry_number ?? '');
        $firstAdmissionYear = (isset($penParts[1]) && is_numeric($penParts[1]) && strlen($penParts[1]) === 4)
            ? (int) $penParts[1]
            : null;

        // Capsule exam results — try trainee_id first, then email→capsule_id, then name fallback
        $capsuleExamResults = \DB::table('capsule_exam_results')
            ->where('trainee_id', $id)
            ->orderByDesc('exam_year')
            ->orderBy('specialty')
            ->get();

        if ($capsuleExamResults->isEmpty()) {
            $traineeEmail = strtolower(trim($trainee->email ?? ''));
            if ($traineeEmail) {
                $capsuleId = \DB::table('capsule_contacts')
                    ->whereRaw('LOWER(email) = ?', [$traineeEmail])
                    ->value('capsule_id');
                if ($capsuleId) {
                    $capsuleExamResults = \DB::table('capsule_exam_results')
                        ->where('capsule_id', $capsuleId)
                        ->orderByDesc('exam_year')
                        ->orderBy('specialty')
                        ->get();
                }
            }
        }

        if ($capsuleExamResults->isEmpty() && !empty($trainee->name)) {
            $capsuleExamResults = \DB::table('capsule_exam_results')
                ->whereRaw("LOWER(contact_name) = LOWER(?)", [trim($trainee->name)])
                ->orderByDesc('exam_year')
                ->orderBy('specialty')
                ->get();
        }

        $header_title = "View Trainee";
        return view('admin.associates.trainees.view',
            compact('trainee', 'header_title', 'linkedCandidate',
                    'programmes', 'countries', 'examYears', 'firstAdmissionYear',
                    'capsuleExamResults', 'entryFees', 'examFees'));
    }

    /**
     * AJAX quick-update for editable tag fields on the trainee profile.
     * Allowed fields: status, exam_year, admission_year, invoice_status,
     *                 admission_letter_status, programme_id, country_id
     */
    public function quickUpdate(Request $request, $id)
    {
        $allowed = ['status','exam_year','admission_year','invoice_status',
                    'admission_letter_status','programme_id','country_id'];

        $field = $request->input('field');
        $value = $request->input('value');

        if (!in_array($field, $allowed)) {
            return response()->json(['error' => 'Invalid field'], 422);
        }

        $trainee = DB::table('trainees')->where('id', $id)->first();
        if (!$trainee) {
            return response()->json(['error' => 'Trainee not found'], 404);
        }

        DB::table('trainees')->where('id', $id)->update([$field => $value, 'updated_at' => now()]);

        // Return the updated display label (for programme/country, return name not id)
        $label = $value;
        if ($field === 'programme_id') {
            $label = DB::table('programmes')->where('id', $value)->value('name') ?? $value;
        } elseif ($field === 'country_id') {
            $label = DB::table('countries')->where('id', $value)->value('country_name') ?? $value;
        }

        return response()->json(['success' => true, 'label' => $label]);
    }

    
    public function add()
    {
        $data['getHospital'] = HospitalModel::getHospital();
        $data['getProgramme'] = Programme::getProgramme();
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Trainee";  
        return view('admin.associates.trainees.add', $data);
    }

    public function import()
    {
        $data['header_title'] = "Import Trainees";
        $data['report']       = session('import_report');
        return view('admin.associates.trainees.import', $data);
    }

    public function importData(Request $request)
    {
        set_time_limit(300);
        ini_set('max_execution_time', 300);

        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:10240',
        ]);

        try {
            $import = new TraineesApplicationImport;
            Excel::import($import, $request->file('file'));

            $report = $import->getReport();
            session(['import_report' => $report]);

            return redirect('admin/associates/trainees/import')
                ->with('import_done', true);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ── Bulk Update (SFS Excel format) ───────────────────────────────────────

    public function bulkUpdate()
    {
        $data['header_title'] = 'Bulk Update Trainees';
        $data['report']       = session('bulk_update_report');
        return view('admin.associates.trainees.bulk_update', $data);
    }

    public function bulkUpdateProcess(Request $request)
    {
        set_time_limit(300);
        ini_set('max_execution_time', 300);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:20480',
        ]);

        try {
            $import = new TraineesExcelUpdateImport;
            Excel::import($import, $request->file('file'));

            session(['bulk_update_report' => $import->getReport()]);

            return redirect('admin/associates/trainees/bulk-update')
                ->with('bulk_done', true);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Bulk update failed: ' . $e->getMessage());
        }
    }

    // ── Add trainee (single) ─────────────────────────────────────────────────

    public function insert(Request $request)
    {
        // Concatenate names
        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");

        // Handle profile image upload
        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $userType = 2; // Assuming '2' represents trainees

        // Create user
        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' =>($request->password),
            'user_type' => $userType
        ]);

        // Create trainee
        Trainee::create([
            'user_id' => $user->id,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'personal_email' => $request->personal_email,
            'gender' => $request->gender,
            'status' => $request->status,
            'profile_image' => $profileImagePath,
            'programme_id' => $request->programme_id,
            'hospital_id' => $request->hospital_id,
            'country_id' => $request->country_id,
            'entry_number' => $request->entry_number,
            'admission_letter_status' => $request->admission_letter_status,
            'invitation_letter_status' => $request->invitation_letter_status,
            'admission_year' => $request->admission_year,
            'training_year' => $request->training_year,
            'exam_year' => $request->exam_year,
            'programme_period' => $request->programme_period,
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'invoice_amount' => $request->invoice_amount,
            'invoice_status' => $request->invoice_status,
            'fee_paid' => $request->fee_paid,
            'sponsor' => $request->sponsor,
            'mode_of_payment' => $request->mode_of_payment,
            'amount_paid' => $request->amount_paid,
            'payment_date' => $request->payment_date,
        ]);

        return redirect('admin/associates/trainees/trainees')->with('success', 'Trainee added successfully');
    }

    public function edit($id)
{
    $trainee = User::getTrainee()->firstWhere('trainee_id', $id);
    if (!$trainee) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
    }
    $data['getHospital']   = HospitalModel::getHospital();
    $data['getProgramme']  = Programme::getProgramme();
    $data['getCountry']    = Country::getCountry();
    $data['getStudyYear']  = DB::table('study_year')->orderBy('programme_id')->orderBy('id')->get();
    $data['header_title']  = "Edit Trainee";
    $data['trainee']       = $trainee;
    return view('admin.associates.trainees.edit_trainee', $data);
}

public function update(Request $request, $id)
{
    $trainee = Trainee::find($id);
    if (!$trainee) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
    }

    $user = User::find($trainee->user_id);

    // Update User
    $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");
    $user->name  = $fullName;
    $user->email = $request->email;
    if ($request->filled('password')) {
        $user->password = $request->password;
    }
    $user->save();

    // Handle profile image upload
    if ($request->hasFile('profile_image')) {
        $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        $trainee->profile_image = $profileImagePath;
    }

    // Update Trainee
    $trainee->firstname               = $request->firstname;
    $trainee->middlename              = $request->middlename;
    $trainee->lastname                = $request->lastname;
    $trainee->personal_email          = $request->personal_email;
    $trainee->gender                  = $request->gender;
    $trainee->status                  = $request->status;
    $trainee->programme_id            = $request->programme_id;
    $trainee->hospital_id             = $request->hospital_id;
    $trainee->country_id              = $request->country_id;
    $trainee->entry_number            = $request->entry_number;
    $trainee->admission_letter_status = $request->admission_letter_status;
    $trainee->invitation_letter_status= $request->invitation_letter_status;
    $trainee->sfs_username             = $request->sfs_username;
    $trainee->sfs_password             = $request->sfs_password;
    $trainee->admission_year          = $request->admission_year ?: null;
    $trainee->exam_year               = $request->exam_year ?: 0;
    $trainee->training_year           = $request->training_year ?: null;
    $trainee->programme_period        = $request->programme_period;
    $trainee->invoice_number          = $request->invoice_number;
    $trainee->invoice_date            = $request->invoice_date ?: null;
    $trainee->invoice_amount          = $request->invoice_amount ?: null;
    $trainee->invoice_status          = $request->invoice_status;
    $trainee->fee_paid                = $request->fee_paid ?? 'No';
    $trainee->sponsor                 = $request->sponsor;
    $trainee->mode_of_payment         = $request->mode_of_payment ?? '';
    $trainee->amount_paid             = $request->amount_paid ?? 0;
    $trainee->payment_date            = $request->payment_date ?: null;
    $trainee->save();

    // Keep matching candidate record in sync
    $this->syncToCandidate($trainee);

    return redirect('admin/associates/trainees/trainees')->with('success', 'Trainee updated successfully');
}

    public function reports()
    {
        return view('admin.associates.trainees.reports', ['header_title' => 'Trainees Analytics']);
    }

    public function reportsList(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all | active | inactive | male | female

        $q = DB::table('trainees')
            ->join('users',      'users.id',      '=', 'trainees.user_id')
            ->join('programmes', 'programmes.id', '=', 'trainees.programme_id')
            ->join('countries',  'countries.id',  '=', 'trainees.country_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'trainees.hospital_id')
            ->where('users.user_type', 2)
            ->select(
                'trainees.id',
                'trainees.entry_number',
                'users.name',
                'trainees.gender',
                'trainees.status',
                'trainees.admission_year',
                'trainees.exam_year',
                'programmes.name as programme_name',
                'countries.country_name',
                'hospitals.name as hospital_name',
                'trainees.personal_email'
            );

        match ($filter) {
            'active'   => $q->where('trainees.status', 'Active'),
            'inactive' => $q->where('trainees.status', 'Inactive'),
            'male'     => $q->where('trainees.gender', 'Male'),
            'female'   => $q->where('trainees.gender', 'Female'),
            default    => null,
        };

        return response()->json($q->orderBy('users.name')->get());
    }

    public function reportsData()
    {
        // KPIs
        $base   = DB::table('trainees')->join('users','users.id','=','trainees.user_id')->where('users.user_type',2);
        $total    = (clone $base)->count();
        $active   = (clone $base)->where('trainees.status','Active')->count();
        $inactive = (clone $base)->where('trainees.status','Inactive')->count();
        $male     = (clone $base)->where('trainees.gender','Male')->count();
        $female   = (clone $base)->where('trainees.gender','Female')->count();

        // By Country (top 15)
        $byCountry = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->join('countries','countries.id','=','trainees.country_id')
            ->where('users.user_type',2)
            ->selectRaw('countries.country_name as label, count(*) as value')
            ->groupBy('countries.country_name')
            ->orderByDesc('value')->limit(15)->get();

        // By Programme
        $byProgramme = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->join('programmes','programmes.id','=','trainees.programme_id')
            ->where('users.user_type',2)
            ->selectRaw('programmes.name as label, count(*) as value')
            ->groupBy('programmes.name')->orderByDesc('value')->get();

        // By Status
        $byStatus = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->where('users.user_type',2)
            ->selectRaw('COALESCE(status,"Unknown") as label, count(*) as value')
            ->groupBy('status')->orderByDesc('value')->get();

        // By Gender
        $byGender = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->where('users.user_type',2)
            ->selectRaw('COALESCE(gender,"Unknown") as label, count(*) as value')
            ->groupBy('gender')->get();

        // By Admission Year trend (2015+)
        $byYear = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->where('users.user_type',2)
            ->whereNotNull('trainees.admission_year')
            ->where('trainees.admission_year','>=',2015)
            ->where('trainees.admission_year','<=',2026)
            ->selectRaw('admission_year as label, count(*) as value')
            ->groupBy('admission_year')->orderBy('admission_year')->get();

        // By Study Year
        $byStudyYear = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->join('study_year','study_year.id','=','trainees.training_year')
            ->where('users.user_type',2)
            ->selectRaw('study_year.name as label, count(*) as value')
            ->groupBy('study_year.name')->orderByDesc('value')->limit(12)->get();

        // Invoice/Payment status
        $byInvoice = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->where('users.user_type',2)
            ->selectRaw('COALESCE(invoice_status,"Pending") as label, count(*) as value')
            ->groupBy('invoice_status')->get();

        // Country summary table (top 20)
        $countryTable = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->join('countries','countries.id','=','trainees.country_id')
            ->where('users.user_type',2)
            ->selectRaw('
                countries.country_name,
                count(*) as total,
                sum(case when trainees.gender="Male"   then 1 else 0 end) as male,
                sum(case when trainees.gender="Female" then 1 else 0 end) as female,
                sum(case when trainees.status="Active" then 1 else 0 end) as active
            ')
            ->groupBy('countries.country_name')
            ->orderByDesc('total')->limit(20)->get();

        return response()->json(compact(
            'total','active','inactive','male','female',
            'byCountry','byProgramme','byStatus','byGender',
            'byYear','byStudyYear','byInvoice','countryTable'
        ));
    }

    public function delete($id)
{
    $user = User::find($id);

    if (!$user) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'User not found');
    }

    $trainee = Trainee::where('user_id', $user->id)->first();

    if (!$trainee) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
    }
    if ($user->user_type != 2) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'User is not a trainee');
    }
    $user->is_deleted = 1;
    $user->save();

    return redirect('admin/associates/trainees/trainees')->with('success', 'Trainee information successfully deleted');
}

    // ── Bidirectional sync helper ─────────────────────────────────────────────
    /**
     * After a trainee is updated, push shared fields to the matching candidate
     * record (matched by user_id). Only fires if a candidate row exists.
     */
    private function syncToCandidate(Trainee $trainee): void
    {
        $candidate = Candidates::where('user_id', $trainee->user_id)->first();
        if (!$candidate) {
            return;
        }

        // NOTE: payment fields (invoice_number, amount_paid, etc.) are intentionally
        // NOT synced. trainees.amount_paid = programme entry fee;
        // candidates.amount_paid = examination fee — they are different fees.
        $candidate->update([
            'firstname'      => $trainee->firstname,
            'middlename'     => $trainee->middlename,
            'lastname'       => $trainee->lastname,
            'personal_email' => $trainee->personal_email,
            'gender'         => $trainee->gender,
            'programme_id'   => $trainee->programme_id,
            'hospital_id'    => $trainee->hospital_id,
            'country_id'     => $trainee->country_id,
            'entry_number'   => $trainee->entry_number,
            'sponsor'        => $trainee->sponsor,
            'exam_year'      => $trainee->exam_year ?: null,
        ]);
    }
}
