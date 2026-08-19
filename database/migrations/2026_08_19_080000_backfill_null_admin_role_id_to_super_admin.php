<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Data-only fix for the "everyone is a Super Admin" bug: User::isSuperAdmin()
// / hasPermission() treated a NULL role_id as a grandfathered Super Admin so
// existing admins wouldn't be locked out the moment role_id was introduced
// (2026_07_17_114722_create_admin_roles_and_permissions). RolesAndPermissionsSeeder
// only ever assigned an explicit role_id to 9 named accounts, so every other
// user_type=1 admin account was left with role_id NULL — which the grandfather
// clause then silently promoted to Super Admin. This backfills every such
// account to the real "Super Admin" role row (preserving their current
// access, just making it explicit), so the follow-up code change that stops
// treating NULL as Super Admin doesn't lock anyone out.
return new class extends Migration
{
    public function up(): void
    {
        $superAdminId = DB::table('roles')->where('name', 'Super Admin')->value('id');

        if (! $superAdminId) {
            // Roles/permissions haven't been seeded yet in this environment —
            // nothing to backfill onto. RolesAndPermissionsSeeder must run
            // before (or instead of) this.
            return;
        }

        DB::table('users')
            ->where('user_type', 1)
            ->whereNull('role_id')
            ->update(['role_id' => $superAdminId]);
    }

    public function down(): void
    {
        // Not reversible — we can't tell which of these rows were
        // legitimately NULL before vs. backfilled here.
    }
};
