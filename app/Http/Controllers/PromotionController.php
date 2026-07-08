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

        // Pre-count only trainees with a valid, active user account (mirrors getTrainee())
        $countMap = DB::table('trainees as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->join('user_roles as ur', function ($j) {
                $j->on('ur.user_id', '=', 'u.id')->where('ur.is_active', 1);
            })
            ->where('u.user_type', 2)
            ->where('u.is_deleted', 0)
            ->whereNotNull('t.training_year')
            ->select('t.training_year', DB::raw('COUNT(*) as cnt'))
            ->groupBy('t.training_year')
            ->pluck('cnt', 't.training_year');

        $data['studyYears']  = $studyYears;
        $data['countMap']    = $countMap;
        $data['examYear']    = $examYear;
        $data['header_title']= 'Promote Trainees to Candidates';
        return view('admin.associates.promotion.promote_to_candidates', $data);
    }

    // AJAX: fetch trainee list for a given study_year_id
    public function traineesPreview()
    {
        $studyYearId = request('study_year_id');
        $examYear    = date('Y');

        if (!$studyYearId) {
            return response()->json([]);
        }

        $trainees = DB::table('trainees as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->join('user_roles as ur', function ($j) {
                $j->on('ur.user_id', '=', 'u.id')->where('ur.is_active', 1);
            })
            ->leftJoin('hospitals as h',  'h.id',  '=', 't.hospital_id')
            ->leftJoin('countries as co', 'co.id', '=', 't.country_id')
            ->leftJoin('programmes as p', 'p.id',  '=', 't.programme_id')
            ->where('u.user_type', 2)
            ->where('u.is_deleted', 0)
            ->where('t.training_year', $studyYearId)
            ->select(
                't.id as trainee_id',
                't.user_id',
                't.firstname', 't.middlename', 't.lastname',
                't.entry_number',
                't.gender',
                'p.name as programme_name',
                'h.name as hospital_name',
                'co.country_name'
            )
            ->orderBy('t.lastname')->orderBy('t.firstname')
            ->get()
            ->map(function ($row) use ($examYear) {
                $row->already_candidate = DB::table('candidates')
                    ->where('user_id', $row->user_id)
                    ->where('exam_year', $examYear)
                    ->exists();
                return $row;
            });

        return response()->json($trainees);
    }

    public function promoteToCandidate_post(Request $request)
    {
        $examYear    = date('Y');
        $traineeIds  = $request->input('trainee_ids', []);   // array of trainee.id values

        if (empty($traineeIds)) {
            return redirect()->back()->with('error', 'No trainees selected for promotion.');
        }

        $trainees = DB::table('trainees as t')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id')
            ->whereIn('t.id', $traineeIds)
            ->select('t.*', 'u.name as full_name')
            ->get();

        if ($trainees->isEmpty()) {
            return redirect()->back()->with('error', 'No trainees found for the selected IDs.');
        }

        $promoted = 0; $skipped = 0; $invalid = 0;
        foreach ($trainees as $t) {
            // Skip trainees not linked to a valid user account
            if (empty($t->user_id) || !DB::table('users')->where('id', $t->user_id)->exists()) {
                $invalid++;
                continue;
            }

            $exists = DB::table('candidates')
                ->where('user_id', $t->user_id)
                ->where('exam_year', $examYear)
                ->exists();

            if ($exists) { $skipped++; continue; }

            DB::table('candidates')->insert([
                'user_id'        => $t->user_id,
                'firstname'      => $t->firstname       ?? '',
                'middlename'     => $t->middlename      ?? '',
                'lastname'       => $t->lastname        ?? '',
                'personal_email' => $t->personal_email  ?? '',
                'gender'         => $t->gender           ?? null,
                'programme_id'   => $t->programme_id,
                'hospital_id'    => $t->hospital_id      ?? null,
                'country_id'     => $t->country_id       ?? null,
                'entry_number'   => $t->entry_number     ?? null,
                'admission_year' => $t->admission_year   ?? null,
                'exam_year'      => $examYear,
                'invoice_status' => 'Pending',
                'fee_paid'       => 'No',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $promoted++;
        }

        $msg = "Successfully promoted <strong>{$promoted}</strong> trainee(s) to <strong>{$examYear}</strong> candidates.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} already registered as {$examYear} candidates — skipped)";
        }
        if ($invalid > 0) {
            $msg .= " ({$invalid} skipped — no linked user account)";
        }

        return redirect()->back()->with('success', $msg);
    }

}
