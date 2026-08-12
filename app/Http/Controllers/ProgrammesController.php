<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProgrammesController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('admin/programmes');
        $data = $response->object();

        return view('admin.programmes.list', [
            'getRecord'    => collect($data->programmes->data ?? $data->programmes ?? []),
            'header_title' => 'Programmes',
        ]);
    }

    public function add()
    {
        return view('admin.programmes.add_programmes', [
            'header_title' => 'Add New Programme',
        ]);
    }

    public function insert(Request $request)
    {
        $this->api->post('admin/programmes', $request->only([
            'name', 'programme_type', 'duration', 'entry_fee', 'exam_fee', 'repeat_fee',
        ]));

        Cache::forget('lookup:programmes');

        return redirect('admin/programmes/list')->with('success', 'Programme successfully created');
    }

    public function view($id)
    {
        $response = $this->api->get("admin/programmes/{$id}");

        if ($response->status() === 404) {
            return redirect('admin/programmes/list')->with('error', 'Programme not found');
        }

        $data = $response->object();

        // examResultsByYear comes back as a JSON object keyed by year — each
        // value is an array of {exam_year, result, n} rows.
        $examResultsByYear = collect((array) ($data->examResultsByYear ?? []));

        return view('admin.programmes.view', [
            'header_title'     => $data->programme->name ?? 'Programme',
            'programme'        => $data->programme,
            'hospitals'        => collect($data->hospitals ?? []),
            'trainees'         => collect($data->trainees ?? []),
            'fellows'          => collect($data->fellows ?? []),
            'examResultsByYear' => $examResultsByYear,
            'examResultsAll'   => collect($data->examResultsAll ?? []),
        ]);
    }

    public function edit($id)
    {
        $response = $this->api->get("admin/programmes/{$id}");

        if ($response->status() === 404) {
            abort(404);
        }

        $data = $response->object();

        return view('admin.programmes.edit_programmes', [
            'header_title' => 'Edit Programme',
            'getRecord'    => $data->programme,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->api->put("admin/programmes/{$id}", $request->only([
            'name', 'programme_type', 'duration', 'entry_fee', 'exam_fee', 'repeat_fee',
        ]));

        Cache::forget('lookup:programmes');

        return redirect('admin/programmes/list')->with('success', 'Programme successfully updated');
    }

    public function delete($id)
    {
        $this->api->delete("admin/programmes/{$id}");

        Cache::forget('lookup:programmes');

        return redirect('admin/programmes/list')->with('success', 'Information successfully Deleted');
    }
}
