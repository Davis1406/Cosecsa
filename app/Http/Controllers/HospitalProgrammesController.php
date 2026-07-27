<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class HospitalProgrammesController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list(Request $request)
    {
        $response = $this->api->get('admin/hospital-programmes', $request->only(['search', 'status', 'programme_id', 'country_id']));
        $data = $response->object();

        return view('admin.hospitalprogrammes.list', [
            'header_title'         => 'Hospital Programmes',
            'getHospitalProgrammes' => collect($data->hospital_programmes ?? []),
            'totalAccreditations'  => $data->total_accreditations ?? 0,
            'totalActive'          => $data->total_active ?? 0,
            'totalExpired'         => $data->total_expired ?? 0,
            'byProgramme'          => collect((array) ($data->by_programme ?? [])),
            'byCountry'            => collect((array) ($data->by_country ?? [])),
        ]);
    }

    public function add()
    {
        $response = $this->api->get('admin/hospital-programmes/create');
        $data = $response->object();

        return view('admin.hospitalprogrammes.add', [
            'header_title' => 'Add New Programme',
            'getHospital'  => collect($data->hospitals ?? []),
            'getProgramme' => collect($data->programmes ?? []),
        ]);
    }

    public function insert(Request $request)
    {
        $response = $this->api->post('admin/hospital-programmes', $request->only([
            'hospital_id', 'programme_id', 'accredited_date', 'expiry_date', 'status',
        ]));

        if ($response->status() === 422) {
            return redirect('admin/hospitalprogrammes/list')->with('error', $response->json('message'));
        }

        return redirect('admin/hospitalprogrammes/list')->with('success', 'Programmes successfully assigned to hospital');
    }

    public function import()
    {
        return view('admin.hospitalprogrammes.import', [
            'header_title' => 'Import Hospital Programmes',
        ]);
    }

    public function importData(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,xlsx,xls|max:2048']);

        $this->api->postWithFile('admin/hospital-programmes/import', [], ['file' => $request->file('file')]);

        return redirect('admin/hospitalprogrammes/list')->with('success', 'Hospital Programmes imported successfully');
    }

    public function edit($id)
    {
        $response = $this->api->get("admin/hospital-programmes/{$id}/edit");

        if ($response->status() === 404) {
            return redirect('admin/hospitalprogrammes/list')->with('error', 'Programme not found');
        }

        $data = $response->object();

        return view('admin.hospitalprogrammes.edit', [
            'header_title'        => 'Edit Programme',
            'hospitalProgramme'   => $data->hospital_programme,
            'assignedProgrammes'  => $data->assigned_programmes ?? [],
            'getHospital'         => collect($data->hospitals ?? []),
            'getProgramme'        => collect($data->programmes ?? []),
        ]);
    }

    public function update(Request $request, $id)
    {
        $response = $this->api->put("admin/hospital-programmes/{$id}", $request->only([
            'programme_id', 'accredited_date', 'expiry_date', 'status',
        ]));

        if ($response->failed()) {
            return redirect('admin/hospitalprogrammes/list')->with('error', $response->json('message'));
        }

        return redirect('admin/hospitalprogrammes/list')->with('success', 'Programme successfully updated');
    }

    public function delete($id)
    {
        $this->api->delete("admin/hospital-programmes/{$id}");

        return redirect('admin/hospitalprogrammes/list')->with('success', 'Information successfully Deleted');
    }
}
