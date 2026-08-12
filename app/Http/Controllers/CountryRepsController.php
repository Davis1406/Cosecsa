<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryRepsController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('country-reps/list-data');
        $data = $response->object();

        return view('admin.associates.reps.list', [
            'getRecord'    => collect($data->reps ?? []),
            'header_title' => "CR's List",
        ]);
    }

    public function view($id)
    {
        $response = $this->api->get("country-reps/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/reps/list')->with('error', 'CR not found');
        }
        $data = $response->object();

        return view('admin.associates.reps.view', [
            'countryRep'      => $data->countryRep,
            'linkedFellow'    => $data->linkedFellow ?? null,
            'header_title'    => 'View CR',
            'relatedProfiles' => $data->relatedProfiles ?? null,
            'countries'       => Country::getCountry(),
        ]);
    }

    public function quickUpdate(Request $request, $id)
    {
        $response = $this->api->post("country-reps/{$id}/quick-update", [
            'field' => $request->input('field'),
            'value' => $request->input('value'),
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function add()
    {
        return view('admin.associates.reps.add', [
            'getCountry'   => Country::getCountry(),
            'header_title' => "Add New CR's",
        ]);
    }

    public function import()
    {
        return view('admin.associates.reps.import', [
            'header_title' => "Import CR's",
        ]);
    }

    public function importData(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,xlsx,xls|max:2048']);

        $this->api->postWithFile('country-reps/import', [], ['file' => $request->file('file')]);

        return redirect('admin/associates/reps/list')->with('success', 'Reps imported successfully');
    }

    public function insert(Request $request)
    {
        $fields = $request->only([
            'name', 'email', 'password', 'country_id', 'cosecsa_email', 'mobile_no',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile('country-reps/', $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post('country-reps/', $fields);
        }

        return redirect('admin/associates/reps/list')->with('success', 'CR added successfully');
    }

    public function edit($id)
    {
        $response = $this->api->get("country-reps/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/reps/list')->with('error', 'CR not found');
        }
        $data = $response->object();

        return view('admin.associates.reps.edit', [
            'countryRep'   => $data->countryRep,
            'getCountry'   => Country::getCountry(),
            'header_title' => 'Edit Country Rep',
        ]);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->only([
            'name', 'email', 'password', 'country_id', 'cosecsa_email', 'mobile_no',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile("country-reps/{$id}", $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post("country-reps/{$id}", $fields);
        }

        return redirect('admin/associates/reps/list')->with('success', 'CR updated successfully');
    }

    public function delete($id)
    {
        $this->api->delete("country-reps/{$id}");

        return redirect('admin/associates/reps/list')->with('success', 'CR information successfully deleted');
    }
}
