<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

// COSECSA ToT (Training of Trainers) roster — distinct from Programme
// Directors (see ProgrammeDirectorController, formerly named TrainerController).
// The roster is bulk-seeded via `php artisan trainers:import-tot-list` in
// cosecsa-api, but individual rows are editable from this admin panel (see
// update()/destroy() below — the "add"/"import" side is still console-only).
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
            'meta'         => $data->meta ?? (object) ['tot_years' => [], 'countries' => [], 'programmes' => [], 'hospitals' => []],
            'header_title' => 'Trainers List',
            // Full reference lists for the Edit modal's dropdowns — meta
            // above only lists values already in use on the roster.
            'allCountries'  => \App\Models\Country::getCountry(),
            'allProgrammes' => \App\Models\Programme::getProgramme(),
            'allHospitals'  => \App\Models\HospitalModel::where('is_deleted', 0)->orderBy('name')->get(['id', 'name']),
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

    public function edit($id)
    {
        $response = $this->api->get("trainers/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/trainers/list')->with('error', 'Trainer not found');
        }
        $data = $response->object();

        return view('admin.associates.trainers.edit', [
            'trainer'       => $data->trainer,
            'allCountries'  => \App\Models\Country::getCountry(),
            'allProgrammes' => \App\Models\Programme::getProgramme(),
            'allHospitals'  => \App\Models\HospitalModel::where('is_deleted', 0)->orderBy('name')->get(['id', 'name']),
            'allTotYears'   => \App\Models\TotYear::orderBy('sort_order')->get(),
            'header_title'  => 'Edit Trainer',
        ]);
    }

    // country_ids[]/tot_year_ids[] are arrays so a trainer can keep more than
    // one of either, same as the underlying trainer_countries/
    // trainer_tot_years pivots (checkbox groups on the edit form).
    public function update(Request $request, $id)
    {
        $this->api->post("trainers/{$id}", $request->only([
            'name', 'organisation', 'hospital_id', 'email', 'country_name',
            'country_ids', 'programme_id', 'specialty_raw', 'tot_year_ids',
            'is_master_trainer', 'is_specialty_surgeon', 'comment',
        ]));

        return redirect('admin/associates/trainers/list')->with('success', 'Trainer updated successfully');
    }

    public function delete($id)
    {
        $this->api->delete("trainers/{$id}");

        return redirect('admin/associates/trainers/list')->with('success', 'Trainer deleted successfully');
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
