<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

// COSECSA ToT (Training of Trainers) roster — distinct from Programme
// Directors (see ProgrammeDirectorController, formerly named TrainerController).
// Read-only from this admin panel for now: the roster is seeded/maintained
// via `php artisan trainers:import-tot-list` in cosecsa-api.
class TrainerController extends Controller
{
    public function __construct(private ApiClient $api) {}

    // Fetches the full roster unfiltered — the list page filters client-side
    // (checkbox filters over the DataTable, like the Programme Directors and
    // other roster pages) so switching filters is instant with no reload.
    // export() below re-applies whatever's checked as real query params so
    // the download matches what's on screen.
    public function list()
    {
        $response = $this->api->get('trainers/list-data');
        $data = $response->object();

        return view('admin.associates.trainers.list', [
            'getRecord'    => collect($data->trainers ?? []),
            'meta'         => $data->meta ?? (object) ['tot_years' => [], 'countries' => [], 'programmes' => []],
            'header_title' => 'Trainers List',
        ]);
    }

    public function view($id)
    {
        $response = $this->api->get("trainers/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/trainers/list')->with('error', 'Trainer not found');
        }
        $data = $response->object();

        return view('admin.associates.trainers.view', [
            'trainer'      => $data->trainer,
            'header_title' => 'View Trainer',
        ]);
    }

    public function quickUpdate(Request $request, $id)
    {
        $response = $this->api->post("trainers/{$id}/quick-update", [
            'field' => $request->input('field'),
            'value' => $request->input('value'),
        ]);

        return response()->json($response->json(), $response->status());
    }

    // Streams the API's CSV straight through to the browser — respects
    // whatever's currently filtered on the list page (same query params).
    public function export(Request $request)
    {
        $response = $this->api->get('trainers/export', $request->only(['search', 'country_id', 'tot_year_id', 'programme_id', 'master_only']));

        return response($response->body(), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cosecsa-trainers-' . now()->format('Ymd_His') . '.csv"',
        ]);
    }
}
