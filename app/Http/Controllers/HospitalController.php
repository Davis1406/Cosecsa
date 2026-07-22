<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\HospitalModel;
use App\Models\Country;
use App\Mail\HospitalAccreditationReminderMail;
use Illuminate\Support\Facades\Mail;
use DB;

class HospitalController extends Controller
{
    // Unified "Hospital Accreditation" hub — merges what used to be two
    // separate menu items (Accredited Hospitals / Hospital Programmes)
    // into one follow-up-focused landing page: every accreditation row,
    // flagged by how close it is to expiring, with a reminder action.
    public function dashboard(Request $request)
    {
        $warningDays = 90;
        $today = now()->toDateString();
        $warningDate = now()->addDays($warningDays)->toDateString();

        $query = DB::table('hospital_programmes as hp')
            ->join('hospitals as h', 'h.id', '=', 'hp.hospital_id')
            ->join('programmes as p', 'p.id', '=', 'hp.programme_id')
            ->leftJoin('countries as c', 'c.id', '=', 'h.country_id')
            ->where('hp.is_delete', 0)
            ->where('h.is_deleted', 0)
            ->select(
                'hp.id', 'hp.hospital_id', 'hp.programme_id', 'hp.accredited_date', 'hp.expiry_date',
                'hp.status', 'hp.last_reminder_sent_at',
                'h.name as hospital_name', 'h.contact_email',
                'p.name as programme_name', 'c.country_name'
            );

        if ($request->filled('country_id')) $query->where('h.country_id', $request->country_id);
        if ($request->filled('programme_id')) $query->where('hp.programme_id', $request->programme_id);
        if ($request->filled('search')) {
            $like = '%' . $request->search . '%';
            $query->where('h.name', 'like', $like);
        }

        $rows = $query->orderBy('hp.expiry_date')->get()->map(function ($r) use ($today, $warningDate) {
            if ($r->status === 'Expired' || $r->expiry_date < $today) {
                $r->flag = 'expired';
            } elseif ($r->expiry_date <= $warningDate) {
                $r->flag = 'expiring_soon';
            } else {
                $r->flag = 'active';
            }
            return $r;
        });

        if ($request->filled('flag')) {
            $rows = $rows->where('flag', $request->flag)->values();
        }

        // Programme director email(s) per hospital, for the "Send Reminder" action.
        $pdEmailsByHospital = DB::table('trainers as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where('u.is_deleted', 0)
            ->select('t.hospital_id', 'u.email', 'u.name')
            ->get()
            ->groupBy('hospital_id');

        $rows = $rows->map(function ($r) use ($pdEmailsByHospital) {
            $pds = $pdEmailsByHospital->get($r->hospital_id, collect());
            $r->reminder_emails = $r->contact_email
                ? array_merge([$r->contact_email], $pds->pluck('email')->all())
                : $pds->pluck('email')->all();
            $r->pd_names = $pds->pluck('name')->implode(', ');
            return $r;
        });

        return view('admin.hospital.dashboard', [
            'header_title'   => 'Hospital Accreditation',
            'rows'           => $rows,
            'countries'      => Country::orderBy('country_name')->get(),
            'programmes'     => \App\Models\Programme::orderBy('name')->get(),
            'filters'        => $request->only(['country_id', 'programme_id', 'flag', 'search']),
            'warningDays'    => $warningDays,
            'totalHospitals' => DB::table('hospitals')->where('is_deleted', 0)->count(),
            'totalAccreditations' => $rows->count(),
            'countActive'    => $rows->where('flag', 'active')->count(),
            'countExpiringSoon' => $rows->where('flag', 'expiring_soon')->count(),
            'countExpired'   => $rows->where('flag', 'expired')->count(),
        ]);
    }

    public function sendReminder($hospitalProgrammeId)
    {
        $row = DB::table('hospital_programmes as hp')
            ->join('hospitals as h', 'h.id', '=', 'hp.hospital_id')
            ->join('programmes as p', 'p.id', '=', 'hp.programme_id')
            ->where('hp.id', $hospitalProgrammeId)
            ->select('hp.*', 'h.name as hospital_name', 'h.contact_email', 'p.name as programme_name')
            ->first();

        if (! $row) {
            return back()->with('error', 'Accreditation record not found.');
        }

        $pdEmails = DB::table('trainers as t')->join('users as u', 'u.id', '=', 't.user_id')
            ->where('t.hospital_id', $row->hospital_id)->where('u.is_deleted', 0)->pluck('u.email')->all();
        $emails = array_unique(array_filter(array_merge([$row->contact_email], $pdEmails)));

        if (empty($emails)) {
            return back()->with('error', 'No contact email on file for ' . $row->hospital_name . ' — add one via Edit Hospital.');
        }

        foreach ($emails as $email) {
            Mail::to($email)->send(new HospitalAccreditationReminderMail($row->hospital_name, $row->programme_name, $row->expiry_date, Auth::user()));
        }

        DB::table('hospital_programmes')->where('id', $hospitalProgrammeId)->update(['last_reminder_sent_at' => now()]);

        return back()->with('success', "Reminder sent to {$row->hospital_name} ({$row->programme_name}).");
    }

    public function sendBulkReminders(Request $request)
    {
        $ids = $request->input('hospital_programme_ids', []);
        $sent = 0;
        $skipped = 0;

        foreach ($ids as $id) {
            $row = DB::table('hospital_programmes as hp')
                ->join('hospitals as h', 'h.id', '=', 'hp.hospital_id')
                ->join('programmes as p', 'p.id', '=', 'hp.programme_id')
                ->where('hp.id', $id)
                ->select('hp.*', 'h.name as hospital_name', 'h.contact_email', 'p.name as programme_name')
                ->first();
            if (! $row) continue;

            $pdEmails = DB::table('trainers as t')->join('users as u', 'u.id', '=', 't.user_id')
                ->where('t.hospital_id', $row->hospital_id)->where('u.is_deleted', 0)->pluck('u.email')->all();
            $emails = array_unique(array_filter(array_merge([$row->contact_email], $pdEmails)));

            if (empty($emails)) {
                $skipped++;
                continue;
            }

            foreach ($emails as $email) {
                Mail::to($email)->send(new HospitalAccreditationReminderMail($row->hospital_name, $row->programme_name, $row->expiry_date, Auth::user()));
            }
            DB::table('hospital_programmes')->where('id', $id)->update(['last_reminder_sent_at' => now()]);
            $sent++;
        }

        return back()->with('success', "Sent {$sent} reminder(s)." . ($skipped ? " {$skipped} skipped (no contact email on file)." : ''));
    }

    public function hospital(){

        $allHospitals = HospitalModel::select('hospitals.*', 'countries.country_name as country_name')
            ->join('countries', 'countries.id', 'hospitals.country_id')
            ->where('hospitals.is_deleted', 0)
            ->orderBy('countries.country_name')
            ->orderBy('hospitals.name')
            ->get();

        $data['getRecord'] = $allHospitals;
        $data['header_title'] = 'Hospitals';

        // Stats for visual report
        $data['totalHospitals']  = $allHospitals->count();
        $data['totalActive']     = $allHospitals->where('status', 0)->count();
        $data['totalInactive']   = $allHospitals->where('status', 1)->count();
        $data['countGovt']       = $allHospitals->where('hospital_type', 1)->count();
        $data['countNGO']        = $allHospitals->where('hospital_type', 2)->count();
        $data['countPrivate']    = $allHospitals->where('hospital_type', 3)->count();
        $data['countUniversity'] = $allHospitals->where('hospital_type', 4)->count();

        // By country (for bar chart) — sort by count desc, take top 20
        $data['byCountry'] = $allHospitals->groupBy('country_name')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        // By type (for doughnut chart)
        $data['byType'] = $allHospitals->groupBy('hospital_type')
            ->map(fn($g) => $g->count());

        return view('admin.hospital.list', $data);
    }

    public function view($id) {
        $hospital = HospitalModel::select('hospitals.*', 'countries.country_name as country_name')
            ->join('countries', 'countries.id', 'hospitals.country_id')
            ->where('hospitals.id', $id)
            ->first();

        if (!$hospital) {
            return redirect('admin/hospital/list')->with('error', 'Hospital not found');
        }

        // Accredited programmes at this hospital
        $programmes = \DB::table('hospital_programmes')
            ->join('programmes', 'programmes.id', '=', 'hospital_programmes.programme_id')
            ->where('hospital_programmes.hospital_id', $id)
            ->where('hospital_programmes.is_delete', 0)
            ->select('hospital_programmes.*', 'programmes.name as programme_name', 'programmes.id as programme_id')
            ->orderBy('programmes.name')
            ->get();

        // Programme directors (trainers) at this hospital
        $trainers = \DB::table('trainers')
            ->join('users', 'users.id', '=', 'trainers.user_id')
            ->join('user_roles', function($j){
                $j->on('user_roles.user_id','=','users.id')
                  ->where('user_roles.role_type', 4)
                  ->where('user_roles.is_active', 1);
            })
            ->where('trainers.hospital_id', $id)
            ->where('users.is_deleted', 0)
            ->select('trainers.id as trainer_id', 'users.name', 'users.email',
                     'trainers.phone_number', 'trainers.assistant_pd', 'trainers.assistant_email')
            ->orderBy('users.name')
            ->get();

        // Trainees at this hospital
        $trainees = \DB::table('trainees')
            ->join('users', 'users.id', '=', 'trainees.user_id')
            ->join('programmes', 'programmes.id', '=', 'trainees.programme_id')
            ->where('trainees.hospital_id', $id)
            ->where('users.is_deleted', 0)
            ->select('trainees.id as trainee_id', 'users.name', 'users.email',
                     'programmes.name as programme_name', 'programmes.id as programme_id',
                     'trainees.admission_year', 'trainees.training_year', 'trainees.status')
            ->orderBy('users.name')
            ->get();

        // Fellows: filtered by programme offered at this hospital + same country.
        // fellows has no hospital_id column, so programme+country is the best available proxy.
        $progIds = $programmes->pluck('programme_id')->unique()->toArray();
        $fellows = collect();
        if (!empty($progIds)) {
            $fellows = \DB::table('fellows')
                ->join('users', 'users.id', '=', 'fellows.user_id')
                ->leftJoin('programmes', 'programmes.id', '=', 'fellows.programme_id')
                ->whereIn('fellows.programme_id', $progIds)
                ->where('fellows.country_id', $hospital->country_id)
                ->where('users.is_deleted', 0)
                ->select('fellows.id as fellow_id', 'users.name', 'users.email',
                         'programmes.name as programme_name', 'programmes.id as programme_id',
                         'fellows.fellowship_year', 'fellows.status')
                ->orderBy('users.name')
                ->get();
        }

        $header_title = $hospital->name;
        return view('admin.hospital.view_hospital',
            compact('hospital', 'header_title', 'programmes', 'trainers', 'trainees', 'fellows'));
    }

    public function add(){
        $data['countries'] = Country::whereIn('id', function($q){
            $q->select('country_id')->from('hospitals')->where('is_deleted', 0);
        })->orWhereIn('country_name', ['Kenya','Uganda','Tanzania','Ethiopia','Rwanda','Zambia','Zimbabwe',
            'Malawi','Namibia','Burundi','Sudan','Mozambique','Botswana','DRC','South Sudan',
            'Madagascar','Niger','Lesotho','Somaliland','Gabon','Eswatini','Togo','Cameroon','Angola'])
          ->orderBy('country_name')->get();
        $data['header_title'] = "Add New Hospital";
        return view('admin.hospital.add_hospital', $data);
    }

    public function insert(Request $request){
        $save = new HospitalModel;
        $save->name          = trim($request->name);
        $save->country_id    = $request->country_id;
        $save->hospital_type = $request->hospital_type ?? 1;
        $save->status        = $request->status;
        $save->contact_email = $request->contact_email;
        $save->save();
        return redirect('admin/hospital/list')->with('success', "Hospital successfully created");
    }

    public function edit($id){
        $data['getRecord'] = HospitalModel::getSingleId($id);
        $data['countries']  = Country::orderBy('country_name')->get();
        if(!empty($data['getRecord'])){
            $data['header_title'] = "Edit Hospital";
            return view('admin.hospital.edit_hospital', $data);
        } else {
            abort(404);
        }
    }

    public function update(Request $request, $id){
        $update = HospitalModel::find($id);
        $update->name          = trim($request->name);
        $update->country_id    = $request->country_id;
        $update->hospital_type = $request->hospital_type ?? $update->hospital_type;
        $update->status        = $request->status;
        $update->contact_email = $request->contact_email;
        $update->save();
        return redirect('admin/hospital/list')->with('success', "Hospital successfully updated");
    }

    public function delete($id){
        $data = HospitalModel::getSingleId($id);
        $data->is_deleted = 1;
        $data->save();
        return redirect('admin/hospital/list')->with('success', "Hospital successfully deleted");
    }
}
