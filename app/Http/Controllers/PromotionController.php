<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromotionModel;
use App\Models\Trainee;
use App\Models\Candidates;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    // List all programmes
    public function promotion()
    {
        $data['getRecord'] = PromotionModel::getStudyYear();
        $data['header_title'] = 'Associate Promotion';
        return view('admin.associates.promotion.promote_trainees', $data);
    }


    public function cadidatesPromotion()
    {
        $data['getRecord'] = PromotionModel::getStudyYear();
        $data['header_title'] = 'Candidates Promotion';
        return view('admin.associates.promotion.promote_candidates', $data);
    }

    public function update(Request $request)
{
    $from_unit = $request->input('from_unit');
    $to_unit = $request->input('to_unit');
    $from_programme_id = $request->input('from_programme_id');
    $to_programme_id = $request->input('to_programme_id');

    // Validate the input
    $request->validate([
        'from_unit' => 'required|integer|exists:study_year,id',
        'to_unit' => 'required|integer|exists:study_year,id',
        'from_programme_id' => 'required|integer|exists:trainees,programme_id',
        'to_programme_id' => 'required|integer|exists:programmes,id',
    ]);

    // Fetch trainees to see if the query matches any records
    $trainees = Trainee::where('training_year', $from_unit)
                       ->where('programme_id', $from_programme_id)
                       ->get();

    // Debugging: Check the results
    if ($trainees->isEmpty()) {
        return redirect()->back()->with('error', 'No trainees found matching the criteria.');
    }

    // For debugging, you can dump the results to inspect
    // dd($trainees->take(5)->toArray());// This will stop execution and show you the matched trainees

    // Proceed with the update if trainees exist
    Trainee::where('training_year', $from_unit)
           ->where('programme_id', $from_programme_id)
           ->update([
               'training_year' => $to_unit,
               'programme_id' => $to_programme_id
           ]);

    return redirect()->back()->with('success', 'Trainees promoted successfully.');
}

    // ── Promote final-year trainees to exam candidates ─────────────────────
    public function promoteToCandidate()
    {
        $examYear   = date('Y');
        $studyYears = DB::table('study_year as sy')
            ->join('programmes as p', 'p.id', '=', 'sy.programme_id')
            ->select('sy.id', 'sy.name as sy_name', 'p.id as prog_id', 'p.name as prog_name', 'p.duration')
            ->orderBy('p.id')->orderBy('sy.id')->get();

        // Pre-count trainees per study_year
        $countMap = DB::table('trainees')
            ->select('training_year', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('training_year')
            ->groupBy('training_year')
            ->pluck('cnt', 'training_year');

        $data['studyYears']  = $studyYears;
        $data['countMap']    = $countMap;
        $data['examYear']    = $examYear;
        $data['header_title']= 'Promote Trainees to Candidates';
        return view('admin.associates.promotion.promote_to_candidates', $data);
    }

    public function promoteToCandidate_post(Request $request)
    {
        $studyYearId = $request->input('study_year_id');
        $examYear    = date('Y');

        $trainees = DB::table('trainees as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where('t.training_year', $studyYearId)
            ->select('t.*', 'u.name as full_name')
            ->get();

        if ($trainees->isEmpty()) {
            return redirect()->back()->with('error', 'No trainees found in the selected study year.');
        }

        $promoted = 0; $skipped = 0;
        foreach ($trainees as $t) {
            // Skip if already a candidate for this exam year
            $exists = DB::table('candidates')
                ->where('user_id', $t->user_id)
                ->where('exam_year', $examYear)
                ->exists();

            if ($exists) { $skipped++; continue; }

            DB::table('candidates')->insert([
                'user_id'        => $t->user_id,
                'firstname'      => $t->firstname  ?? '',
                'middlename'     => $t->middlename ?? '',
                'lastname'       => $t->lastname   ?? '',
                'personal_email' => $t->personal_email ?? '',
                'gender'         => $t->gender      ?? null,
                'programme_id'   => $t->programme_id,
                'hospital_id'    => $t->hospital_id ?? null,
                'country_id'     => $t->country_id  ?? null,
                'entry_number'   => $t->entry_number ?? null,
                'admission_year' => $t->admission_year ?? null,
                'exam_year'      => $examYear,
                'invoice_status' => 'Pending',
                'fee_paid'       => 'No',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $promoted++;
        }

        return redirect()->back()->with('success',
            "Promoted $promoted trainee(s) to $examYear candidates. ($skipped already registered — skipped)");
    }

}
