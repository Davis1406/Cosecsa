<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use App\Models\HospitalModel;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('trainers/list-data');
        $data = $response->object();

        return view('admin.associates.trainers.list', [
            'getRecord'    => collect($data->trainers ?? []),
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
            'trainer'         => $data->trainer,
            'header_title'    => 'View Trainer',
            'relatedProfiles' => $data->relatedProfiles ?? null,
        ]);
    }

    public function add()
    {
        return view('admin.associates.trainers.add', [
            'getHospital'  => HospitalModel::getHospital(),
            'getCountry'   => collect([]),
            'getProgramme' => collect([]),
            'header_title' => 'Add New Trainer',
        ]);
    }

    public function import()
    {
        return view('admin.associates.trainers.import', [
            'header_title' => 'Import Trainers',
        ]);
    }

    public function importData(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,xlsx,xls|max:2048']);

        $this->api->postWithFile('trainers/import', [], ['file' => $request->file('file')]);

        return redirect('admin/associates/trainers/list')->with('success', 'Trainers imported successfully');
    }

    public function insert(Request $request)
    {
        $fields = $request->only([
            'name', 'email', 'password', 'phone_number', 'hospital_id',
            'assistant_pd', 'assistant_email', 'mobile_no',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile('trainers/', $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post('trainers/', $fields);
        }

        return redirect('admin/associates/trainers/list')->with('success', 'Trainer added successfully');
    }

    public function edit($id)
    {
        $response = $this->api->get("trainers/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/trainers/list')->with('error', 'Trainer not found');
        }
        $data = $response->object();

        return view('admin.associates.trainers.edit', [
            'trainer'      => $data->trainer,
            'getHospital'  => HospitalModel::getHospital(),
            'getCountry'   => collect([]),
            'getProgramme' => collect([]),
            'header_title' => 'Edit Trainer',
        ]);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->only([
            'name', 'email', 'password', 'phone_number', 'hospital_id',
            'assistant_pd', 'assistant_email', 'mobile_no',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile("trainers/{$id}", $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post("trainers/{$id}", $fields);
        }

        return redirect('admin/associates/trainers/list')->with('success', 'Trainer updated successfully');
    }

    public function delete($id)
    {
        $this->api->delete("trainers/{$id}");

        return redirect('admin/associates/trainers/list')->with('success', 'Trainer information successfully deleted');
    }
}
