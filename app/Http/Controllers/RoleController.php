<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('admin/roles');
        $data = $response->object();

        return view('admin.roles.list', [
            'getRecord'    => collect($data->roles ?? []),
            'header_title' => 'Roles & Permissions',
        ]);
    }

    public function add()
    {
        // Modules are static config — no need for an extra API round-trip.
        return view('admin.roles.form', [
            'header_title' => 'Add New Role',
            'modules'      => config('admin_permissions.modules'),
            'role'         => null,
            'checkedKeys'  => [],
        ]);
    }

    public function insert(Request $request)
    {
        $response = $this->api->post('admin/roles', $request->only([
            'name', 'description', 'permissions',
        ]));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message'));
        }

        return redirect('admin/roles/list')->with('success', 'Role created successfully');
    }

    public function edit($id)
    {
        $response = $this->api->get("admin/roles/{$id}");

        if ($response->status() === 404) {
            abort(404);
        }

        $data = $response->object();

        return view('admin.roles.form', [
            'header_title' => 'Edit Role',
            'modules'      => (array) ($data->modules ?? config('admin_permissions.modules')),
            'role'         => $data->role,
            'checkedKeys'  => $data->checked_keys ?? [],
        ]);
    }

    public function update($id, Request $request)
    {
        $response = $this->api->put("admin/roles/{$id}", $request->only([
            'name', 'description', 'permissions',
        ]));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message'));
        }

        return redirect('admin/roles/list')->with('success', 'Role updated successfully');
    }

    public function delete($id)
    {
        $response = $this->api->delete("admin/roles/{$id}");

        if ($response->failed()) {
            return redirect('admin/roles/list')->with('error', $response->json('message'));
        }

        return redirect('admin/roles/list')->with('success', 'Role deleted');
    }
}
