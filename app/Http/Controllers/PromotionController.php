<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromotionModel;
use App\Models\Trainee; // Assuming you have a Trainee model

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

}
