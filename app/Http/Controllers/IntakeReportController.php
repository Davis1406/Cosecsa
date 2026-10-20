<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

/**
 * Associates > Intake Reports. The page and its numbers come from cosecsa-api (internal/intake-report/*), which builds one
 * entry per person, programme and PEN year from trainees, candidates and fellows.
 */
class IntakeReportController extends Controller
{
    private const FILTERS = ['from_year', 'to_year', 'programme_id', 'programme_type', 'country_id', 'status', 'gender', 'source', 'year_basis'];

    public function __construct(private ApiClient $api) {}

    // GET admin/associates/intake-reports
    public function index()
    {
        $response = $this->api->get('intake-report/filters');

        return view('admin.associates.intake-reports.index', [
            'header_title' => 'Intake Reports',
            'filters' => $response->successful() ? $response->json() : null,
        ]);
    }

    // GET admin/associates/intake-reports/data — KPIs, year x programme matrix and data checks
    public function data(Request $request)
    {
        $response = $this->api->get('intake-report/data', $request->only(self::FILTERS));

        return response()->json($response->json() ?? [], $response->status());
    }

    // GET admin/associates/intake-reports/entries — one row per entry for the detail table
    public function entries(Request $request)
    {
        $response = $this->api->get('intake-report/entries', $request->only(self::FILTERS));

        return response()->json($response->json() ?? [], $response->status());
    }
}
