<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Master Admin = everything Super Admin has (same permission set, also
    // an is_system-protected role), plus exclusive Progressive Reports
    // access-approval rights and invisibility on the Super-Admin-facing
    // Admin List. See User::isMasterAdmin()/canApproveProgressReportAccess().
    public function up(): void
    {
        $now = now();

        $masterAdminId = DB::table('roles')->where('name', 'Master Admin')->value('id');
        if (! $masterAdminId) {
            $masterAdminId = DB::table('roles')->insertGetId([
                'name'        => 'Master Admin',
                'description' => 'Everything Super Admin has, plus exclusive Progressive Reports edit-access approval and is hidden from the standard Admin List.',
                'is_system'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $superAdminId = DB::table('roles')->where('name', 'Super Admin')->value('id');
        if ($superAdminId) {
            $permissionIds = DB::table('role_permissions')->where('role_id', $superAdminId)->pluck('permission_id');
            foreach ($permissionIds as $permissionId) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $masterAdminId)->where('permission_id', $permissionId)->exists();
                if (! $exists) {
                    DB::table('role_permissions')->insert([
                        'role_id'       => $masterAdminId,
                        'permission_id' => $permissionId,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]);
                }
            }
        }

        // Davis Kondamwali (user id 1)
        DB::table('users')->where('id', 1)->update(['role_id' => $masterAdminId]);
    }

    public function down(): void
    {
        $masterAdminId = DB::table('roles')->where('name', 'Master Admin')->value('id');
        if (! $masterAdminId) {
            return;
        }

        $superAdminId = DB::table('roles')->where('name', 'Super Admin')->value('id');
        DB::table('users')->where('role_id', $masterAdminId)->update(['role_id' => $superAdminId]);
        DB::table('role_permissions')->where('role_id', $masterAdminId)->delete();
        DB::table('roles')->where('id', $masterAdminId)->delete();
    }
};
