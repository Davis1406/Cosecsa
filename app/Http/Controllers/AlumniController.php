<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('fellows/alumni');
        $data = $response->object();

        return view('admin.associates.alumni.list', [
            'alumni'       => collect($data->alumni ?? []),
            'header_title' => 'Alumni',
        ]);
    }

    public function reports()
    {
        return view('admin.associates.alumni.reports', ['header_title' => 'Alumni Analytics']);
    }

    public function reportsData()
    {
        $response = $this->api->get('fellows/alumni/reports');

        return response()->json($response->json(), $response->status());
    }
}
