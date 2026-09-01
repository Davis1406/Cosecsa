<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FetchesAssociateNotes;
use App\Services\ApiClient;
use App\Models\HospitalModel;
use Illuminate\Http\Request;

// Formerly TrainerController — the `trainers` table/endpoints have always
// actually been Programme Directors (one per hospital/programme). Renamed to
// make room for a genuine Trainers page (COSECSA ToT roster) — see
// TrainerController (new).
class ProgrammeDirectorController extends Controller
{
    use FetchesAssociateNotes;

    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('programme-directors/list-data');
        $data = $response->object();

        return view('admin.associates.programme-directors.list', [
            'getRecord'    => collect($data->programme_directors ?? []),
            'header_title' => 'Programme Directors List',
        ]);
    }

    public function view($id)
    {
        $response = $this->api->get("programme-directors/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/programme-directors/list')->with('error', 'Programme Director not found');
        }
        $data = $response->object();

        return view('admin.associates.programme-directors.view', [
            'pd'              => $data->programme_director,
            'header_title'    => 'View Programme Director',
            'relatedProfiles' => $data->relatedProfiles ?? null,
            'hospitals'       => HospitalModel::getHospital(),
            'notes'           => $this->associateNotes('programme_director', $id),
        ]);
    }

    public function quickUpdate(Request $request, $id)
    {
        $response = $this->api->post("programme-directors/{$id}/quick-update", [
            'field' => $request->input('field'),
            'value' => $request->input('value'),
        ]);

        return response()->json($response->json(), $response->status());
    }

    // AJAX counterpart to update() — same fields, same API endpoint, but
    // returns JSON instead of a redirect so it can be used from a modal
    // (e.g. the hospital view's "Edit Programme Director" modal) without
    // navigating away. Api\ProgrammeDirectorController::update() overwrites
    // every one of these columns from the request, so callers must send the
    // PD's current hospital_id/programme_id/mobile_no even when only editing
    // name/email/phone/assistant fields, or those get nulled out.
    public function ajaxUpdate(Request $request, $id)
    {
        $fields = $request->only([
            'name', 'email', 'phone_number', 'hospital_id', 'programme_id',
            'assistant_pd', 'assistant_email', 'mobile_no',
        ]);

        $response = $this->api->post("programme-directors/{$id}", $fields);

        return response()->json($response->json(), $response->status());
    }

    public function add()
    {
        return view('admin.associates.programme-directors.add', [
            'getHospital'  => HospitalModel::getHospital(),
            'getCountry'   => collect([]),
            'getProgramme' => collect([]),
            'header_title' => 'Add New Programme Director',
        ]);
    }

    public function import()
    {
        return view('admin.associates.programme-directors.import', [
            'header_title' => 'Import Programme Directors',
        ]);
    }

    public function importData(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,xlsx,xls|max:2048']);

        $this->api->postWithFile('programme-directors/import', [], ['file' => $request->file('file')]);

        return redirect('admin/associates/programme-directors/list')->with('success', 'Programme Directors imported successfully');
    }

    public function insert(Request $request)
    {
        $fields = $request->only([
            'name', 'email', 'password', 'phone_number', 'hospital_id',
            'assistant_pd', 'assistant_email', 'mobile_no',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile('programme-directors/', $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post('programme-directors/', $fields);
        }

        return redirect('admin/associates/programme-directors/list')->with('success', 'Programme Director added successfully');
    }

    public function edit($id)
    {
        $response = $this->api->get("programme-directors/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/programme-directors/list')->with('error', 'Programme Director not found');
        }
        $data = $response->object();

        return view('admin.associates.programme-directors.edit', [
            'pd'           => $data->programme_director,
            'getHospital'  => HospitalModel::getHospital(),
            'getCountry'   => collect([]),
            'getProgramme' => collect([]),
            'header_title' => 'Edit Programme Director',
        ]);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->only([
            'name', 'email', 'password', 'phone_number', 'hospital_id',
            'assistant_pd', 'assistant_email', 'mobile_no',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile("programme-directors/{$id}", $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post("programme-directors/{$id}", $fields);
        }

        return redirect('admin/associates/programme-directors/list')->with('success', 'Programme Director updated successfully');
    }

    public function delete($id)
    {
        $this->api->delete("programme-directors/{$id}");

        return redirect('admin/associates/programme-directors/list')->with('success', 'Programme Director information successfully deleted');
    }
}
