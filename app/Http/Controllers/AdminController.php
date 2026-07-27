<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('admin/users');
        $data = $response->object();
        $admins = collect($data->admins ?? []);

        return view('admin.list', [
            'getRecord' => new \Illuminate\Pagination\LengthAwarePaginator(
                $admins,
                $admins->count(),
                25,
                \Illuminate\Pagination\Paginator::resolveCurrentPage(),
                ['path' => url('admin/list')]
            ),
            'header_title' => 'Admin List',
        ]);
    }

    public function add()
    {
        $response = $this->api->get('admin/users/assignable-roles');
        $data = $response->object();

        return view('admin.add', [
            'header_title' => 'Add New Admin',
            'roles'        => collect($data->roles ?? []),
        ]);
    }

    public function insert(Request $request)
    {
        $response = $this->api->post('admin/users', $request->only([
            'name', 'email', 'password', 'role_id',
        ]));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message'));
        }

        return redirect('admin/list')->with('success', 'Admin successfully created');
    }

    public function edit($id)
    {
        $userRes  = $this->api->get("admin/users/{$id}");
        $rolesRes = $this->api->get('admin/users/assignable-roles');

        if ($userRes->status() === 404) {
            abort(404);
        }

        $userData  = $userRes->object();
        $rolesData = $rolesRes->object();

        return view('admin.edit', [
            'header_title' => 'Edit Admin',
            'getRecord'    => $userData->admin,
            'roles'        => collect($rolesData->roles ?? []),
        ]);
    }

    public function update($id, Request $request)
    {
        $fields = $request->only(['name', 'email', 'role_id', 'password']);

        if ($request->hasFile('profile_image')) {
            $response = $this->api->postWithFile(
                "admin/users/{$id}",
                $fields,
                ['profile_image' => $request->file('profile_image')]
            );
        } else {
            $response = $this->api->post("admin/users/{$id}", $fields);
        }

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message'));
        }

        return redirect('admin/list')->with('success', 'Information successfully updated');
    }

    public function delete($id)
    {
        $response = $this->api->delete("admin/users/{$id}");

        if ($response->failed()) {
            return redirect('admin/list')->with('error', $response->json('message'));
        }

        return redirect('admin/list')->with('success', 'Information successfully Deleted');
    }
}
