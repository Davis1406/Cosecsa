<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use DB;

class AdminController extends Controller
{
    public function list()
    {
        $data['getRecord'] = User::getAdmin();
        $data['header_title'] = "Admin List";
        return view('admin.list', $data);
    }

    public function add()
    {
        $data['header_title'] = "Add New Admin";
        return view('admin.add', $data);
    }

    public function insert(Request $request)
    {
        request()->validate([
            'email' => 'required|email|unique:users'
        ]);

        DB::beginTransaction();
        try {
            $user = new User;
            $user->name = trim($request->name);
            $user->email = trim($request->email);
            $user->password = Hash::make($request->password);
            $user->user_type = 1; // Admin
            $user->save();

            // ✅ Add to user_roles
            DB::table('user_roles')->insert([
                'user_id' => $user->id,
                'role_type' => 1, // Admin
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect('admin/list')->with('success', "Admin successfully created");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating admin: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $data['getRecord'] = User::getSingleId($id);
        if (!empty($data['getRecord'])) {
            $data['header_title'] = "Edit Admin";
            return view('admin.edit', $data);
        } else {
            abort(404);
        }
    }

    public function update($id, Request $request)
    {
        request()->validate([
            'email' => 'required|email|unique:users,email,' . $id
        ]);

        DB::beginTransaction();
        try {
            $user = User::getSingleId($id);
            $user->name = trim($request->name);
            $user->email = trim($request->email);
            $user->user_type = 1;

            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // ✅ Ensure user_roles entry is updated or inserted
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'role_type' => 1,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );

            DB::commit();
            return redirect('admin/list')->with('success', "Information successfully updated");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating admin: ' . $e->getMessage());
        }
    }


    public function delete($id)
    {
        $user = User::getSingleId($id);

        if (!$user) {
            return redirect('admin/list')->with('error', 'Admin not found');
        }

        \DB::beginTransaction();
        try {
            $user->is_deleted = 1;
            $user->save();

            // ✅ Deactivate user role
            \DB::table('user_roles')
                ->where('user_id', $user->id)
                ->update([
                    'is_active' => 0,
                    'updated_at' => now(),
                ]);

            \DB::commit();
            return redirect('admin/list')->with('success', "Information successfully Deleted");
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect('admin/list')->with('error', 'Error deleting admin: ' . $e->getMessage());
        }
    }
}
