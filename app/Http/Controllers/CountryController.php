<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('admin/countries');
        $data = $response->object();

        return view('admin.countries.list', [
            'countries'    => collect($data->countries ?? []),
            'header_title' => 'Countries',
        ]);
    }

    public function view($id)
    {
        $response = $this->api->get("admin/countries/{$id}");

        if ($response->status() === 404) {
            return redirect('admin/countries/list')->with('error', 'Country not found');
        }

        $data = $response->object();

        return view('admin.countries.view', [
            'header_title' => $data->country->country_name ?? 'Country',
            'country'      => $data->country,
            'hospitals'    => collect($data->hospitals ?? []),
            'trainees'     => collect($data->trainees ?? []),
            'fellows'      => collect($data->fellows ?? []),
            'members'      => collect($data->members ?? []),
            'reps'         => collect($data->reps ?? []),
            'programmeDirectors' => collect($data->programme_directors ?? []),
            'examiners'    => collect($data->examiners ?? []),
            'trainers'     => collect($data->trainers ?? []),
        ]);
    }

    // The visual "Updates on candidates and trainees" report of one country; the numbers come from cosecsa-api.
    public function report(Request $request, $id = null)
    {
        $countries = collect($this->api->get('admin/countries')->object()->countries ?? []);
        $report = null;

        if ($id) {
            $response = $this->api->get("admin/countries/{$id}/report", array_filter($request->only('exam_year', 'intake')));
            if ($response->failed()) {
                return redirect('admin/countries/report')->with('error', 'Could not build the report: ' . ($response->json('message') ?: 'no answer from the server.'));
            }
            $report = $response->json();
        }

        return view('admin.countries.report', [
            'header_title' => 'Country report',
            'countries'    => $countries,
            'countryId'    => $id,
            'report'       => $report,
        ]);
    }
}
