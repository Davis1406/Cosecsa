<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class FellowLabelController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function index()
    {
        $response = $this->api->get('settings/fellow-labels');
        $data = $response->object();

        return view('admin.settings.fellow_labels.index', [
            'labels'       => collect($data->labels ?? []),
            'header_title' => 'Fellow Labels – Settings',
        ]);
    }

    public function store(Request $request)
    {
        $response = $this->api->post('settings/fellow-labels', $request->only([
            'name', 'color', 'description',
        ]));

        if ($response->failed()) {
            return back()->withInput()->withErrors($response->json('errors') ?? ['error' => $response->json('message')]);
        }

        return redirect('admin/settings/fellow-labels')->with('success', $response->json('message'));
    }

    public function update(Request $request, $id)
    {
        $response = $this->api->put("settings/fellow-labels/{$id}", $request->only([
            'name', 'color', 'description', 'is_active',
        ]));

        if ($response->failed()) {
            return back()->withInput()->withErrors($response->json('errors') ?? ['error' => $response->json('message')]);
        }

        return redirect('admin/settings/fellow-labels')->with('success', $response->json('message'));
    }

    public function destroy($id)
    {
        $response = $this->api->delete("settings/fellow-labels/{$id}");

        if ($response->failed()) {
            return redirect('admin/settings/fellow-labels')->with('error', $response->json('message'));
        }

        return redirect('admin/settings/fellow-labels')->with('success', $response->json('message'));
    }
}
