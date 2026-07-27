<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function promotion()
    {
        $response = $this->api->get('promotions/study-years');
        $data = $response->object();

        return view('admin.associates.promotion.promote_trainees', [
            'getRecord'    => collect($data->study_years ?? []),
            'header_title' => 'Associate Promotion',
        ]);
    }

    public function cadidatesPromotion()
    {
        $response = $this->api->get('promotions/study-years');
        $data = $response->object();

        return view('admin.associates.promotion.promote_candidates', [
            'getRecord'    => collect($data->study_years ?? []),
            'header_title' => 'Candidates Promotion',
        ]);
    }

    public function update(Request $request)
    {
        $response = $this->api->post('promotions/trainees', $request->only([
            'from_unit', 'to_unit', 'from_programme_id', 'to_programme_id',
        ]));

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message'));
        }

        return redirect()->back()->with('success', $response->json('message'));
    }

    public function promoteToCandidate()
    {
        $response = $this->api->get('promotions/to-candidates');
        $data = $response->object();

        return view('admin.associates.promotion.promote_to_candidates', [
            'header_title' => 'Promote Trainees to Candidates',
            'studyYears'   => collect($data->study_years ?? []),
            'countMap'     => collect((array) ($data->count_map ?? [])),
            'examYear'     => $data->exam_year ?? date('Y'),
        ]);
    }

    public function traineesPreview(Request $request)
    {
        $response = $this->api->get('promotions/trainees-preview', $request->only(['study_year_id']));

        return response()->json($response->json(), $response->status());
    }

    public function promoteToCandidate_post(Request $request)
    {
        $response = $this->api->post('promotions/to-candidates', $request->only(['trainee_ids']));

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message'));
        }

        $data = $response->json();
        $promoted = $data['promoted'] ?? 0;
        $skipped  = $data['skipped'] ?? 0;
        $invalid  = $data['invalid'] ?? 0;
        $examYear = $data['exam_year'] ?? date('Y');

        $msg = "Successfully promoted <strong>{$promoted}</strong> trainee(s) to <strong>{$examYear}</strong> candidates.";
        if ($skipped > 0) $msg .= " ({$skipped} already registered as {$examYear} candidates — skipped)";
        if ($invalid > 0) $msg .= " ({$invalid} skipped — no linked user account)";

        return redirect()->back()->with('success', $msg);
    }
}
