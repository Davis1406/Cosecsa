<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function list()
    {
        $data['getRecord'] = Role::withCount('users')->orderBy('name')->get();
        $data['header_title'] = "Roles & Permissions";
        return view('admin.roles.list', $data);
    }

    public function add()
    {
        $data['header_title'] = "Add New Role";
        $data['modules'] = config('admin_permissions.modules');
        $data['role'] = null;
        $data['checkedKeys'] = [];
        return view('admin.roles.form', $data);
    }

    public function insert(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:roles,name']);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => trim($request->name),
                'description' => trim($request->description ?? ''),
                'is_system' => false,
            ]);

            $this->syncPermissions($role, $request->input('permissions', []));

            DB::commit();
            return redirect('admin/roles/list')->with('success', 'Role created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating role: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $data['header_title'] = "Edit Role";
        $data['modules'] = config('admin_permissions.modules');
        $data['role'] = $role;
        $data['checkedKeys'] = $role->permissions()->pluck('key')->all();
        return view('admin.roles.form', $data);
    }

    public function update($id, Request $request)
    {
        $role = Role::findOrFail($id);

        $request->validate(['name' => 'required|string|max:255|unique:roles,name,' . $id]);

        if ($role->is_system) {
            return back()->with('error', 'This is a protected system role and cannot be edited.');
        }

        DB::beginTransaction();
        try {
            $role->name = trim($request->name);
            $role->description = trim($request->description ?? '');
            $role->save();

            $this->syncPermissions($role, $request->input('permissions', []));

            // Permission changes should apply immediately, not after the
            // 5-minute cache in User::hasPermission() expires on its own.
            foreach ($role->users()->pluck('id') as $userId) {
                Cache::forget("user_permissions_{$userId}");
            }

            DB::commit();
            return redirect('admin/roles/list')->with('success', 'Role updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating role: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system) {
            return redirect('admin/roles/list')->with('error', 'This is a protected system role and cannot be deleted.');
        }
        if ($role->users()->exists()) {
            return redirect('admin/roles/list')->with('error', 'Cannot delete a role that is still assigned to admin accounts — reassign them first.');
        }

        $role->delete();
        return redirect('admin/roles/list')->with('success', 'Role deleted');
    }

    protected function syncPermissions(Role $role, array $keys): void
    {
        $ids = Permission::whereIn('key', $keys)->pluck('id', 'key');
        $role->permissions()->sync($ids->values()->all());
    }
}
