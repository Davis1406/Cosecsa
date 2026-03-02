<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FellowsModel;
use App\Models\FellowLabel;
use App\Models\Country;
use App\Models\UserRole;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FellowshipImport;

class FellowsController extends Controller
{
    public function list()
    {
        $data['header_title'] = 'Fellows';
        $data['getFellows'] = User::getFellows();
        return view('admin.associates.fellows.list', $data);
    }

    public function view($id)
    {
        $fellow = User::getFellows()->firstWhere('fellow_id', $id);
        if (!$fellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }

        // Load subscriptions
        $subscriptions = \DB::table('fellow_subscriptions')
            ->where('fellow_id', $fellow->fellow_id)
            ->orderBy('year', 'desc')
            ->get();

        // Load exam history
        $examHistory = collect();
        $candidate = \DB::table('candidates')->where('user_id', $fellow->user_id)->first();
        if ($candidate) {
            $mcs = \DB::table('mcs_results')->where('candidate_id', $candidate->id)
                ->select('exam_year', 'exam_type', 'overall', 'remarks', 'created_at')
                ->get()->map(fn($r) => (object) array_merge((array) $r, ['source' => 'MCS']));
            $gs = \DB::table('gs_results')->where('candidate_id', $candidate->id)
                ->select('exam_year', 'exam_type', 'overall', 'remarks', 'created_at')
                ->get()->map(fn($r) => (object) array_merge((array) $r, ['source' => 'FCS GS']));
            $examHistory = $mcs->merge($gs)->sortByDesc('exam_year')->values();
        }

        // Load labels
        $fellowRecord = FellowsModel::find($fellow->fellow_id);
        $allLabels = FellowLabel::where('is_active', true)->orderBy('name')->get();
        $assignedLabels = $fellowRecord ? $fellowRecord->labels : collect();
        $currentLabelIds = $assignedLabels->pluck('id')->toArray();

        $header_title = "View Fellow";
        return view('admin.associates.fellows.view', compact(
            'fellow', 'header_title', 'subscriptions', 'examHistory',
            'allLabels', 'assignedLabels', 'currentLabelIds'
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

        FellowsModel::create([
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
            'category_id'                   => $request->category_id,
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
            'category_id'                   => $request->category_id,
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

        if ($user->user_type != 7) {
            return redirect('admin/associates/fellows/list')->with('error', 'User is not a fellow');
        }

        \DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_type', 7)
            ->update(['is_active' => 0, 'updated_at' => now()]);

        return redirect('admin/associates/fellows/list')->with('success', 'Fellow successfully Deleted');
    }
}
