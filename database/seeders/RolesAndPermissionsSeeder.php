<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    // role name => [module keys granted "manage" (implies view), module keys granted "view" only]
    protected const ROLE_GRANTS = [
        'Examinations Officer' => [
            'manage' => ['examiners', 'candidates', 'promotions'],
            'view'   => ['dashboard', 'trainees'],
        ],
        'Education Officer' => [
            'manage' => ['trainees', 'trainers', 'promotions', 'lookups'],
            'view'   => ['dashboard', 'candidates'],
        ],
        'Finance Officer' => [
            'manage' => ['fees'],
            'view'   => ['dashboard', 'trainees', 'candidates', 'fellows', 'members'],
        ],
        'Administrative Officer' => [
            'manage' => ['fellows', 'country_reps'],
            'view'   => ['dashboard', 'trainees'],
        ],
        'Academic Records Assistant' => [
            'manage' => ['fellows', 'trainees', 'members'],
            'view'   => ['dashboard'],
        ],
        'Admissions Assistant' => [
            'manage' => ['salesforce', 'trainees'],
            'view'   => ['dashboard', 'candidates'],
        ],
        'Research and Patient Outcomes Coordinator' => [
            'manage' => [],
            'view'   => ['dashboard', 'examiners', 'fellows', 'trainees'],
        ],
        'Managing Editor' => [
            'manage' => [],
            'view'   => ['dashboard', 'fellows', 'examiners'],
        ],
        'Chief Executive Officer' => [
            'manage' => [],
            'view'   => ['dashboard', 'trainees', 'candidates', 'trainers', 'country_reps', 'fellows', 'members', 'examiners', 'promotions', 'capsule', 'salesforce', 'fees', 'settings', 'lookups'],
        ],
        'IT and Examinations Assistant' => [
            'manage' => ['examiners', 'candidates', 'promotions', 'capsule', 'salesforce', 'settings', 'lookups'],
            'view'   => ['dashboard'],
        ],
    ];

    public function run(): void
    {
        $modules = config('admin_permissions.modules');

        // ── Permissions: one "view" + one "manage" per module ──
        $permissionIds = [];
        foreach ($modules as $key => $label) {
            foreach (['view', 'manage'] as $suffix) {
                $permission = Permission::updateOrCreate(
                    ['key' => "{$key}.{$suffix}"],
                    ['module' => $label, 'label' => ucfirst($suffix) . " {$label}"]
                );
                $permissionIds["{$key}.{$suffix}"] = $permission->id;
            }
        }

        // ── Super Admin: protected system role, every permission ──
        $superAdmin = Role::updateOrCreate(
            ['name' => 'Super Admin'],
            ['description' => 'Full access to every module, including managing other admins and roles.', 'is_system' => true]
        );
        $superAdmin->permissions()->sync(array_values($permissionIds));

        // ── Scoped roles from the grant map above ──
        foreach (self::ROLE_GRANTS as $name => $grants) {
            $role = Role::updateOrCreate(['name' => $name], ['description' => '', 'is_system' => false]);

            $keys = [];
            foreach ($grants['manage'] as $module) {
                $keys[] = "{$module}.manage";
                $keys[] = "{$module}.view"; // manage implies view
            }
            foreach ($grants['view'] as $module) {
                $keys[] = "{$module}.view";
            }
            $keys = array_unique($keys);

            $ids = array_map(fn ($k) => $permissionIds[$k], array_filter($keys, fn ($k) => isset($permissionIds[$k])));
            $role->permissions()->sync($ids);
        }

        // ── Assign roles to the secretariat accounts already created ──
        $roleIdByName = Role::pluck('id', 'name');

        $assignments = [
            // The four full-access admins
            1    => 'Super Admin',   // Davis Kondamwali (admin@cosecsa.org)
            3    => 'Super Admin',   // Amani Pascal
            7827 => 'Super Admin',   // Laurence Kisanga
            8032 => 'Super Admin',   // Niraj B.
            // Scoped secretariat roles
            16679 => 'Academic Records Assistant', // Edna Herman
            17991 => 'Chief Executive Officer',     // Stella Itungu
            17992 => 'Administrative Officer',      // Diana Kaiza
            17993 => 'Finance Officer',              // Jonathan Omongole
            17994 => 'Managing Editor',              // Vincent Kipkorir
            17995 => 'Research and Patient Outcomes Coordinator', // Godfrey Sama
        ];

        foreach ($assignments as $userId => $roleName) {
            $roleId = $roleIdByName[$roleName] ?? null;
            if (! $roleId) continue;

            DB::table('users')->where('id', $userId)->update(['role_id' => $roleId]);

            // Activate login for the 5 secretariat accounts that were
            // created without a user_roles row (no admin access) until this
            // scoped-permission system existed to bound what they can see.
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $userId, 'role_type' => 1],
                ['is_active' => 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
