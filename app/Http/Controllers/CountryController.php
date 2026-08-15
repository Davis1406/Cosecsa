<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;

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
        ]);
    }
}
